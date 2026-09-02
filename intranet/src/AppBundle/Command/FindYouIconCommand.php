<?php

declare(strict_types=1);

namespace AppBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:icons:explore',
    description: 'Generates an alphabetical visual catalog of your local icons.',
)]
class FindYouIconCommand extends Command
{
    public function __construct(
        private readonly ParameterBagInterface $params,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('html', null, InputOption::VALUE_NONE, 'Generate the visual HTML gallery');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $iconsDir = $this->params->get('kernel.project_dir').'/assets/icons';

        if (!is_dir($iconsDir)) {
            $io->error('Folder assets/icons not found.');

            return Command::FAILURE;
        }

        $finder = (new Finder())->files()->in($iconsDir)->name('*.svg')->sortByName();

        $icons = [];
        foreach ($finder as $file) {
            $relativePath = $file->getRelativePathname();
            $identifier = str_replace(['/', '.svg', '\\'], [':', '', ':'], $relativePath);

            $icons[] = [
                'identifier' => $identifier,
                'svg' => $file->getContents(),
            ];
        }

        if ($input->getOption('html')) {
            $this->generateHtml($icons, $io);
        } else {
            $io->success(\count($icons).' icons found. Run with --html to see the gallery.');
        }

        return Command::SUCCESS;
    }

    private function generateHtml(array $icons, SymfonyStyle $io): void
    {
        $htmlFile = $this->params->get('kernel.project_dir').'/var/icons_explorer.html';

        $cardsHtml = '';
        foreach ($icons as $icon) {
            $cardsHtml .= "
            <div class='card' data-id='{$icon['identifier']}'>
                <div class='preview'>{$icon['svg']}</div>
                <div class='code'>ux_icon('{$icon['identifier']}')</div>
            </div>";
        }

        $html = "
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>Ux Icons Explorer</title>
            <style>
                body { font-family: 'Inter', system-ui, sans-serif; background: #f8fafc; color: #1e293b; padding: 40px; margin: 0; }
                .container { max-width: 1200px; margin: 0 auto; }
                .header { position: sticky; top: 0; background: #f8fafc; padding: 20px 0; z-index: 10; border-bottom: 1px solid #e2e8f0; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; }
                .search-container { flex-grow: 1; max-width: 400px; margin-left: 20px; }
                #searchInput { width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; outline: none; transition: 0.2s; }
                #searchInput:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
                .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; }
                .card { background: #fff; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid #e2e8f0; transition: 0.2s; cursor: pointer; position: relative; }
                .card:hover { border-color: #3b82f6; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                .card:active { transform: scale(0.98); }
                .preview svg { width: 32px; height: 32px; color: #475569; pointer-events: none; }
                .code { font-size: 11px; font-family: monospace; font-weight: 600; margin-top: 10px; color: #1e293b; background: #f1f5f9; padding: 4px; border-radius: 4px; overflow-wrap: break-word; pointer-events: none; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Ux Icons <small id='count' style='font-weight: normal; color: #64748b;'>(".\count($icons).")</small></h1>
                    <div class='search-container'>
                        <input type='text' id='searchInput' placeholder='Search by identifier (ex: heroicons:check)...' autofocus>
                    </div>
                </div>
                <div class='grid' id='iconGrid'>$cardsHtml</div>
            </div>

            <script>
                const searchInput = document.getElementById('searchInput');
                const cards = document.querySelectorAll('.card');
                const countDisplay = document.getElementById('count');

                searchInput.addEventListener('input', (e) => {
                    const term = e.target.value.toLowerCase();
                    let visibleCount = 0;

                    cards.forEach(card => {
                        const id = card.getAttribute('data-id').toLowerCase();
                        if (id.includes(term)) {
                            card.style.display = 'block';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    countDisplay.textContent = '(' + visibleCount + ')';
                });

                cards.forEach(card => {
                    card.addEventListener('click', () => {
                        const textToCopy = card.querySelector('.code').textContent;
                        navigator.clipboard.writeText(textToCopy).then(() => {
                            const originalBackground = card.style.background;
                            card.style.background = '#dcfce7';
                            card.style.borderColor = '#22c55e';
                            
                            setTimeout(() => {
                                card.style.background = originalBackground;
                                card.style.borderColor = '#e2e8f0';
                            }, 400);
                        });
                    });
                });
            </script>
        </body>
        </html>";

        file_put_contents($htmlFile, $html);
        $io->success('Gallery generated: var/icons_explorer.html');
    }
}
