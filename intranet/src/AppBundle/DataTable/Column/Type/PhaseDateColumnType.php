<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;

class PhaseDateColumnType extends AbstractColumnType
{
    public function buildValueView(ColumnValueView $view, ColumnInterface $column, array $options): void
    {
        $view->vars = array_merge($view->vars, [
            'label_classes' => $this->backgroundColorLabel($view->vars['data']), $column,
        ]);
    }

    public function getParent(): ?string
    {
        return LabelColumnType::class;
    }

    private function backgroundColorLabel($date): array|string
    {
        if (null !== $date) {
            $date = (new \DateTime($date))->setTime(0, 0);
            $now = (new \DateTime('now'))->setTime(0, 0);

            $status = match (true) {
                $now < $date => 'success',
                $now > $date => 'danger',
                default => 'warning',
            };

            return [$date->format('d.m.Y') => $status];
        }

        return '';
    }
}
