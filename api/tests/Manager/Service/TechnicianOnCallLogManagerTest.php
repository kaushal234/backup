<?php

declare(strict_types=1);

namespace App\Tests\Manager\Service;

use App\Doctrine\Change;
use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use App\Manager\Service\TechnicianOnCallLogManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallLogManagerTest extends TestCase
{
    use ProphecyTrait;

    public function testCreationComment(): void
    {
        $createdBy = new People();
        $createdBy->setFirstname('Firstname');
        $createdBy->setLastname('Lastname');

        $entity = new TechnicianOnCall();
        $entity->createdBy = $createdBy;
        $entity->createdAt = new \DateTime();

        $change = new Change();
        $change->setEntity($entity);

        $translatorProphecy = $this->prophesize(TranslatorInterface::class);
        $translatorProphecy->trans('toc.comment.creation', Argument::cetera())->shouldBeCalledOnce()->willReturn('toc creation');

        $technicianOnCallLogManager = new TechnicianOnCallLogManager($translatorProphecy->reveal());
        $comment = $technicianOnCallLogManager->creationComment($change);

        self::assertSame('toc creation', $comment);
    }

    public function testNoCommentIfNoChange(): void
    {
        $change = new Change();
        $translatorProphecy = $this->prophesize(TranslatorInterface::class);
        $translatorProphecy->trans(Argument::cetera())->shouldNotBeCalled();

        $technicianOnCallLogManager = new TechnicianOnCallLogManager($translatorProphecy->reveal());
        $comment = $technicianOnCallLogManager->logToComment($change);

        self::assertNull($comment);
    }

    public function testCommentIfChange(): void
    {
        $changeSet = [
            'title' => ['old title', 'new title'],
            'description' => ['old description', 'new description'],
            'status' => ['old status', 'new status'],
        ];

        $change = new Change();
        $change->setChangeset($changeSet);

        $translatorProphecy = $this->prophesize(TranslatorInterface::class);
        $translatorProphecy->trans('toc.comment.title', Argument::cetera())->shouldBeCalledOnce()->willReturn('toc.comment.title');
        $translatorProphecy->trans('toc.comment.description', Argument::cetera())->shouldBeCalledOnce()->willReturn('toc.comment.description');
        $translatorProphecy->trans('toc.comment.status', Argument::cetera())->shouldBeCalledOnce()->willReturn('toc.comment.status');

        $technicianOnCallLogManager = new TechnicianOnCallLogManager($translatorProphecy->reveal());
        $comment = $technicianOnCallLogManager->logToComment($change);

        $expected = <<<'EOF'
            toc.comment.title
            toc.comment.description
            toc.comment.status
            EOF;

        self::assertSame($expected, $comment);
    }
}
