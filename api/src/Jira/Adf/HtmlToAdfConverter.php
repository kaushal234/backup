<?php

declare(strict_types=1);

namespace App\Jira\Adf;

use Alvest\FeatureDoc\Attribute\FeatureDoc;

#[FeatureDoc(path: 'html-to-adf-converter.md')]
class HtmlToAdfConverter
{
    private const BLOCK_TAGS = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'blockquote'];

    private const HEADING_TAGS = ['h1' => 1, 'h2' => 2, 'h3' => 3, 'h4' => 4, 'h5' => 5, 'h6' => 6];

    private const MARK_TAGS = [
        'strong' => 'strong',
        'b' => 'strong',
        'em' => 'em',
        'i' => 'em',
        'u' => 'underline',
    ];

    public function convert(string $html): array
    {
        $root = $this->parse($html);
        $content = null === $root ? [] : $this->convertChildrenAsBlocks($root);

        return ['content' => $this->nonEmptyBlocks($content), 'type' => 'doc', 'version' => 1];
    }

    /**
     * Parses the given HTML fragment into a single root element, without the surrounding
     * <html>/<body> DOMDocument would otherwise add.
     *
     * - The `<?xml encoding="utf-8" ?>` prefix is a legacy libxml trick to force UTF-8 decoding:
     *   loadHTML() otherwise assumes ISO-8859-1 unless the markup carries its own <meta charset>.
     * - LIBXML_HTML_NOIMPLIED prevents libxml from wrapping our fragment in <html><body>, so the
     *   <div> we supply becomes documentElement directly.
     * - LIBXML_HTML_NODEFDTD suppresses the default doctype libxml would otherwise inject.
     *
     * Malformed HTML is parsed on a best-effort basis (libxml's recovery mode) and any parse
     * errors are discarded on purpose - a broken comment must not fail to sync. If a caller ever
     * needs to detect that the input was malformed, read libxml_get_errors() before the
     * libxml_clear_errors() call below.
     *
     * TODO: once the project runs PHP >= 8.4, replace this whole method with
     * Dom\HTMLDocument::createFromString($html, LIBXML_NOERROR)->documentElement - the new DOM
     * API is a real WHATWG-compliant HTML5 parser, handles UTF-8 natively, and needs none of the
     * workarounds above (no encoding prefix, no NOIMPLIED/NODEFDTD flags, no internal-errors dance).
     */
    private function parse(string $html): ?\DOMElement
    {
        $document = new \DOMDocument();
        $previousSetting = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div>'.$html.'</div>',
            \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);

        return $document->documentElement;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function convertChildrenAsBlocks(\DOMNode $node): array
    {
        $blocks = [];
        $inlineBuffer = [];

        foreach (iterator_to_array($node->childNodes, false) as $child) {
            if ($child instanceof \DOMElement && \in_array($child->tagName, self::BLOCK_TAGS, true)) {
                $this->flushParagraph($inlineBuffer, $blocks);
                $blocks[] = $this->convertBlockElement($child);
                continue;
            }

            array_push($inlineBuffer, ...$this->convertInline($child, []));
        }

        $this->flushParagraph($inlineBuffer, $blocks);

        return $blocks;
    }

    private function flushParagraph(array &$inlineBuffer, array &$blocks): void
    {
        if ([] !== $inlineBuffer && $this->isSignificantContent($inlineBuffer)) {
            $blocks[] = $this->paragraphNode($inlineBuffer);
        }

        $inlineBuffer = [];
    }

    /**
     * @param array<int, array<string, mixed>> $content
     *
     * @return array<string, mixed>
     */
    private function paragraphNode(array $content): array
    {
        return ['content' => $content, 'type' => 'paragraph'];
    }

    /**
     * @param array<int, array<string, mixed>> $inlineNodes
     */
    private function isSignificantContent(array $inlineNodes): bool
    {
        foreach ($inlineNodes as $node) {
            if ('text' !== $node['type'] || '' !== mb_trim($node['text'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function convertBlockElement(\DOMElement $element): array
    {
        if (\array_key_exists($element->tagName, self::HEADING_TAGS)) {
            return [
                'type' => 'heading',
                'attrs' => ['level' => self::HEADING_TAGS[$element->tagName]],
                'content' => $this->convertInlineChildren($element),
            ];
        }

        return match ($element->tagName) {
            'p' => $this->paragraphNode($this->convertInlineChildren($element)),
            'ul' => ['type' => 'bulletList', 'content' => $this->convertListItems($element)],
            'ol' => ['type' => 'orderedList', 'content' => $this->convertListItems($element)],
            'blockquote' => ['type' => 'blockquote', 'content' => $this->nonEmptyBlocks($this->convertChildrenAsBlocks($element))],
            default => throw new \LogicException(\sprintf('Unsupported block tag "%s".', $element->tagName)),
        };
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     *
     * @return array<int, array<string, mixed>>
     */
    private function nonEmptyBlocks(array $blocks): array
    {
        return [] !== $blocks ? $blocks : [$this->paragraphNode([])];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function convertListItems(\DOMElement $list): array
    {
        $items = [];

        foreach (iterator_to_array($list->childNodes, false) as $child) {
            if (!$child instanceof \DOMElement || 'li' !== $child->tagName) {
                continue;
            }

            $items[] = ['type' => 'listItem', 'content' => $this->nonEmptyBlocks($this->convertChildrenAsBlocks($child))];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function convertInlineChildren(\DOMElement $element): array
    {
        return $this->convertInlineChildrenWithMarks($element, []);
    }

    /**
     * @param array<string, mixed> $marks
     *
     * @return array<int, array<string, mixed>>
     */
    private function convertInline(\DOMNode $node, array $marks): array
    {
        if ($node instanceof \DOMText) {
            return '' === $node->wholeText ? [] : [$this->textNode($node->wholeText, $marks)];
        }

        if (!$node instanceof \DOMElement) {
            return [];
        }

        if ('br' === $node->tagName) {
            return [['type' => 'hardBreak']];
        }

        if ('a' === $node->tagName) {
            $marks['link'] = ['href' => $node->getAttribute('href')];

            return $this->convertInlineChildrenWithMarks($node, $marks);
        }

        if (\array_key_exists($node->tagName, self::MARK_TAGS)) {
            $marks[self::MARK_TAGS[$node->tagName]] = true;

            return $this->convertInlineChildrenWithMarks($node, $marks);
        }

        // Unknown inline tag (e.g. span): transparent, recurse keeping current marks.
        return $this->convertInlineChildrenWithMarks($node, $marks);
    }

    /**
     * @param array<string, mixed> $marks
     *
     * @return array<int, array<string, mixed>>
     */
    private function convertInlineChildrenWithMarks(\DOMElement $element, array $marks): array
    {
        $inline = [];
        foreach (iterator_to_array($element->childNodes, false) as $child) {
            array_push($inline, ...$this->convertInline($child, $marks));
        }

        return $inline;
    }

    /**
     * @param array<string, mixed> $marks
     *
     * @return array<string, mixed>
     */
    private function textNode(string $text, array $marks): array
    {
        $node = ['type' => 'text', 'text' => $text];

        if ([] === $marks) {
            return $node;
        }

        $node['marks'] = array_map(
            static fn (string $type, $attrs) => true === $attrs ? ['type' => $type] : ['type' => $type, 'attrs' => $attrs],
            array_keys($marks),
            array_values($marks),
        );

        return $node;
    }
}
