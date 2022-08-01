<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\AssertElementColor;
use Laravel\Dusk\Browser;

trait EntryModal {

    use AccountOrAccountTypeSelector;
    use AssertElementColor;
    use EntryModalSelectors;
    use FileDragNDrop;
    use TagsInput;
    use ToggleButton;

    // colours
    private $_color_expense_switch_expense = "";
    private $_color_expense_switch_income = "";

    private function initEntryModalColours(){
        $this->_color_expense_switch_expense = $this->tailwindColors->yellow(400);
        $this->_color_expense_switch_income = $this->tailwindColors->teal(500);
    }

    protected function assertConfirmedButtonActive(Browser $modal){
        $modal
            ->assertMissing($this->_selector_modal_entry_confirmed)
            ->assertChecked($this->_selector_modal_entry_confirmed)
            ->assertSee($this->_label_btn_confirmed);

        $this->assertParentElementBackgroundColor($modal, $this->_selector_modal_entry_confirmed, $this->tailwindColors->green(400));
        $this->assertParentElementTextColor($modal, $this->_selector_modal_entry_confirmed, $this->tailwindColors->white());
    }

    protected function assertConfirmedButtonInactive(Browser $modal){
        $modal
            ->assertMissing($this->_selector_modal_entry_confirmed)
            ->assertNotChecked($this->_selector_modal_entry_confirmed)
            ->assertSee($this->_label_btn_confirmed);

        $this->assertParentElementBackgroundColor($modal, $this->_selector_modal_entry_confirmed, $this->tailwindColors->white());
        $this->assertParentElementTextColor($modal, $this->_selector_modal_entry_confirmed, $this->tailwindColors->gray(400));
    }

    protected function interactWithConfirmButton(Browser $modal){
        // need to directly interact using JavaScript rather than dusk api as the dusk api doesn't allow us to access parent nodes of a selector
        $script = <<<JS
document.querySelector('$this->_selector_modal_entry_confirmed').parentNode.click();
JS;
        $modal->script($script);
    }

    protected function assertEntryValueCurrency(Browser $modal, string $currency_character){
        // check currency icon in input#entry-value
        $entry_value_currency = $modal->text($this->_selector_modal_entry_field_value." + span.currency-symbol");
        $this->assertStringContainsString($currency_character, $entry_value_currency);
    }

    // TODO: open empty/new entry
    //     May already exist

    // TODO: open an existing entry
    //      May already exist

    // TODO: close modal - footer "Cancel"
    // TODO: close modal - header (X)

    // TODO: save modal

    // TODO: delete modal

    // TODO: lock modal

    // TODO: unlock modal

    // TODO: fill in fields

    // TODO: click transfer entry button

}
