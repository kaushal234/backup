<?php

declare(strict_types=1);

namespace ActivityBundle\Twig\Extension;

use ActivityBundle\Manager\ActivityManager;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ActivityExtension extends AbstractExtension
{
    /** @var ActivityManager */
    protected $manager;

    public function __construct(ActivityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'resourceActivities',
                fn (Environment $twig, string $resourceIriId, string $backUrl = '/', bool $displayComments = true, bool $displayLogs = true): string => $this->renderEntityActivities($twig, $resourceIriId, $backUrl, $displayComments, $displayLogs),
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
        ];
    }

    /**
     * Display the activity (logs + comments) of a resource.
     *
     * @param string $resourceIriId
     * @param string $backUrl
     * @param bool   $displayComments
     * @param bool   $displayLogs
     *
     * @return string
     */
    public function renderEntityActivities(
        Environment $twig,
        $resourceIriId,
        $backUrl = '/',
        $displayComments = true,
        $displayLogs = true,
    ) {
        return $twig->render(
            '@ActivityBundle/activity/show.html.twig',
            $this->manager->show($resourceIriId, $displayComments, $displayLogs, $backUrl)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'activity';
    }
}
