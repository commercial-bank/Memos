<?php

namespace App\Livewire\Card;

use Livewire\Component;

class DashboardCard extends Component
{
   public $title;
    public $value;
    public $description;
    public $icon;
    public $trend; // 'up', 'down', 'neutral' pour une flèche de tendance
    public $trendValue; // Par exemple, '+5%'

    public function mount($title, $value, $description, $icon, $trend = 'neutral', $trendValue = '')
    {
        $this->title = $title;
        $this->value = $value;
        $this->description = $description;
        $this->icon = $icon;
        $this->trend = $trend;
        $this->trendValue = $trendValue;
    }

    public function render()
    {
        return view('livewire.card.dashboard-card');
    }
}
