<?php

namespace App\Livewire;

use Livewire\Component;

class ThemeToggle extends Component
{
    public bool $isDark = false;

    public function mount()
    {
        $this->isDark = request()->cookie('theme') === 'dark' || 
            (!request()->hasCookie('theme') && 
             config('app.dark_mode_by_default', false));
    }

    public function toggle()
    {
        $this->isDark = !$this->isDark;
        
        $theme = $this->isDark ? 'dark' : 'light';
        
        cookie()->queue('theme', $theme, 60 * 24 * 365, null, null, false, false);
        
        $this->dispatch('theme-changed', isDark: $this->isDark);
    }

    public function render()
    {
        return view('livewire.theme-toggle');
    }
}