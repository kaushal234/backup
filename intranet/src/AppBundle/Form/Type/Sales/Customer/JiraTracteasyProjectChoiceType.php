<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use ApiBundle\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JiraTracteasyProjectChoiceType extends AbstractType
{
    public function __construct(private readonly Client $client)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = [];
        $jiraTracteasyProjects = $this->client->get('/jira_tracteasy/tracteasy_helpdesk_projects')['hydra:member'];
        foreach ($jiraTracteasyProjects as $project) {
            $choices[\sprintf('%s - %s', $project['key'], $project['name'])] = $project['key'];
        }

        $resolver->setDefaults([
            'choices' => $choices,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
