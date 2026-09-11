<?php

namespace App\Livewire\Components;

use App\Services\SetupWizardService;
use Livewire\Attributes\On;
use Livewire\Component;

class SetupWizard extends Component
{
    public bool $showModal = false;
    public array $steps = [];
    public int $completedCount = 0;
    public int $totalCount = 6;
    public int $percentage = 0;
    public bool $isComplete = false;
    public ?array $nextStep = null;

    public function mount()
    {
        $this->loadStatus();

        // Check if user is currently on an active setup configuration route
        $isSetupRoute = request()->routeIs('settings.*') 
            || request()->routeIs('products.categories') 
            || request()->routeIs('accounts.index');

        // Automatically pop up modal on initial entry/visit if ANY setup data is still missing
        if (!$this->isComplete && !session()->get('setup_wizard_dismissed', false) && !$isSetupRoute) {
            $this->showModal = true;
        }
    }

    #[On('check-and-open-setup-wizard')]
    public function checkAndOpenWizard()
    {
        $this->loadStatus();
        // Only open modal if there are still pending steps
        if (!$this->isComplete) {
            session()->forget('setup_wizard_dismissed');
            $this->showModal = true;
        }
        // If fully complete — do nothing, just refresh status silently
    }

    #[On('open-setup-wizard')]
    public function openWizard()
    {
        $this->loadStatus();
        if ($this->isComplete) {
            // All done — no need to show the modal, just silently ignore or redirect
            return;
        }
        session()->forget('setup_wizard_dismissed');
        $this->showModal = true;
    }

    #[On('refresh-setup-status')]
    public function loadStatus()
    {
        $this->steps = SetupWizardService::getSteps();
        $this->completedCount = SetupWizardService::getCompletedCount();
        $this->totalCount = SetupWizardService::getTotalCount();
        $this->percentage = SetupWizardService::getPercentage();
        $this->isComplete = SetupWizardService::isComplete();
        $this->nextStep = SetupWizardService::getNextIncompleteStep();
    }

    public function closeWizard()
    {
        $this->showModal = false;
        session()->put('setup_wizard_dismissed', true);
    }

    public function continueSetup()
    {
        $this->loadStatus();
        if ($this->nextStep && isset($this->nextStep['route'])) {
            $this->showModal = false;
            return redirect()->route($this->nextStep['route']);
        }
    }

    public function navigateTo(string $route)
    {
        $this->showModal = false;
        return redirect()->route($route);
    }

    public function render()
    {
        return view('livewire.components.setup-wizard');
    }
}
