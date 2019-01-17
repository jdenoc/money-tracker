<?php

namespace Tests\Browser;

use App\Http\Controllers\Api\EntryController;
use Facebook\WebDriver\WebDriverBy;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\HomePageSelectors;

class TransferModalTest extends DuskTestCase {

    use DatabaseMigrations;
    use HomePageSelectors;

    public function setUp(){
        parent::setUp();
        Artisan::call('db:seed', ['--class'=>'UiSampleDatabaseSeeder']);
    }

    public function testTransferModalNotVisibleByDefault(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertMissing($this->_selector_modal_transfer);
        });
    }

    public function testOpenTransferModalFromNavbarElement(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openTransferModal()
                ->assertVisible($this->_selector_modal_transfer);
        });
    }

    public function testModalHeaderHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openTransferModal()
                ->with($this->_selector_modal_transfer.' '.$this->_selector_modal_head, function($modal){
                    $modal
                        ->assertSee("Transfer")
                        ->assertVisible($this->_selector_modal_btn_close);
                });
        });
    }

    public function testModalBodyHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openTransferModal()
                ->with($this->_selector_modal_transfer.' '.$this->_selector_modal_body, function($modal){
                    $modal
                        ->assertSee("Date:")
                        ->assertVisible($this->_selector_modal_transfer_field_date)
                        ->assertInputValue($this->_selector_modal_transfer_field_date, "")

                        ->assertSee("Value:")
                        ->assertVisible($this->_selector_modal_transfer_field_value)
                        ->assertInputValue($this->_selector_modal_transfer_field_value, "")

                        ->assertDontSee($this->_label_account_type_meta_last_digits)
                        ->assertDontSee($this->_label_account_type_meta_account_name)

                        ->assertSee("From:")
                        ->assertVisible($this->_selector_modal_transfer_field_from)
                        ->assertSelected($this->_selector_modal_transfer_field_from, "")
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_from)
                        ->assertSelectHasOption($this->_selector_modal_transfer_field_from, "0")

                        ->assertSee("To:")
                        ->assertVisible($this->_selector_modal_transfer_field_to)
                        ->assertSelected($this->_selector_modal_transfer_field_to, "")
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_to)
                        ->assertSelectHasOption($this->_selector_modal_transfer_field_to, "0")

                        ->assertSee("Memo:")
                        ->assertVisible($this->_selector_modal_transfer_field_memo)
                        ->assertInputValue($this->_selector_modal_transfer_field_memo, "")

                        ->assertSee("Tags:")
                        ->assertVisible($this->_selector_modal_transfer_field_tags)  // auto-complete tags-input field
                        ->assertInputValue($this->_selector_modal_transfer_field_tags, "")

                        ->assertVisible($this->_selector_modal_transfer_field_upload) // drag-n-drop file upload field
                        ->with($this->_selector_modal_transfer_field_upload, function($file_upload){
                            $file_upload->assertSee($this->_label_file_upload);
                        });
                });
        });
    }

    public function testModalFooterHasCorrectElements(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openTransferModal()
                ->with($this->_selector_modal_transfer.' '.$this->_selector_modal_foot, function($modal){
                    $modal
                        ->assertSee($this->_label_btn_cancel)
                        ->assertVisible($this->_selector_modal_transfer_btn_cancel)
                        ->assertSee("Save")
                        ->assertVisible($this->_selector_modal_transfer_btn_save);
                });
        });
    }

    public function testCloseTransferModalWithXButtonInHeader(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openTransferModal()
                ->with($this->_selector_modal_transfer.' '.$this->_selector_modal_head, function($modal){
                    $modal->click($this->_selector_modal_btn_close);
                })
                ->assertMissing($this->_selector_modal_transfer);
        });
    }

    public function testCloseTransferModalWithCancelButtonInFooter(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openTransferModal()
                ->with($this->_selector_modal_transfer.' '.$this->_selector_modal_foot, function($modal){
                    $modal->click($this->_selector_modal_transfer_btn_cancel);
                })
                ->assertMissing($this->_selector_modal_transfer);
        });
    }

    public function testCloseTransferModalWithHotkey(){
        $this->markTestSkipped("hotkey not working on transfer-modal yet");
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openTransferModal()
                ->keys('', "{control}", "{escape}") // ["{control}", "{escape}"] didn't work
                ->assertMissing($this->_selector_modal_transfer);
        });
    }

    public function testTransferValueConvertsIntoDecimalOfTwoPlaces(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openTransferModal()
                ->with($this->_selector_modal_transfer, function($modal){
                    $modal
                        ->type($this->_selector_modal_transfer_field_value, "F15sae.92fwf3w")
                        ->click($this->_selector_modal_transfer_field_date)
                        ->assertInputValue($this->_selector_modal_transfer_field_value, "15.92");
                });
        });
    }

    public function testFillFieldsToEnabledSaveButton(){
        $faker = FakerFactory::create();
        $all_account_types = $this->getApiAccountTypes();
        $account_types = $faker->randomElements($all_account_types, 2);

        $this->browse(function(Browser $browser) use ($account_types){
            // get locale date string from browser
            $browser_locale_date = $this->getBrowserLocaleDate($browser);
            $browser_locale_date = $this->processLocaleDateForTyping($browser_locale_date);

            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openTransferModal()
                ->with($this->_selector_modal_transfer, function($modal) use ($browser_locale_date){
                    $modal->type($this->_selector_modal_transfer_field_date, $browser_locale_date);
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->with($this->_selector_modal_transfer, function($modal){
                    $modal->type($this->_selector_modal_transfer_field_value, "123.45");
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->waitUntilMissing($this->_selector_modal_transfer_field_from_is_loading, HomePage::WAIT_SECONDS)
                ->waitUntilMissing($this->_selector_modal_transfer_field_to_is_loading, HomePage::WAIT_SECONDS)

                ->with($this->_selector_modal_transfer, function($modal) use ($account_types){
                    $modal
                        ->select($this->_selector_modal_transfer_field_from, $account_types[0]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_from);
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->with($this->_selector_modal_transfer, function($modal) use ($account_types){
                    $modal
                        ->select($this->_selector_modal_transfer_field_to, $account_types[1]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_to);
                })
                ->assertTransferModalSaveButtonIsDisabled()

                ->with($this->_selector_modal_transfer, function($modal){
                    $modal
                        ->type($this->_selector_modal_transfer_field_memo, "Test transfer")
                        ->click($this->_selector_modal_transfer_field_date);
                })
                ->assertTransferModalSaveButtonIsNotDisabled()

                // set "from" field to "[External account]"
                ->with($this->_selector_modal_transfer, function($modal) use ($account_types){
                    $modal
                        ->select($this->_selector_modal_transfer_field_from, 0)
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_from)
                        ->select($this->_selector_modal_transfer_field_to, $account_types[0]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_to);
                })
                ->assertTransferModalSaveButtonIsNotDisabled()

                // set "to" field to "[External account]"
                ->with($this->_selector_modal_transfer, function($modal) use ($account_types){
                    $modal
                        ->select($this->_selector_modal_transfer_field_to, 0)
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_to)
                        ->select($this->_selector_modal_transfer_field_from, $account_types[0]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_from);
                })
                ->assertTransferModalSaveButtonIsNotDisabled()

                // match the "to" field to the "from" field
                ->with($this->_selector_modal_transfer, function($modal) use ($account_types){
                    $modal
                        ->select($this->_selector_modal_transfer_field_from, $account_types[0]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_from)
                        ->select($this->_selector_modal_transfer_field_to, $account_types[0]['id'])
                        ->assertVisible($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertVisible($this->_selector_modal_transfer_meta_last_digits_to);
                })
                ->assertTransferModalSaveButtonIsDisabled()

                // match the "to" field to the "from" field; they're both set to "[External account]"
                ->with($this->_selector_modal_transfer, function($modal) use ($account_types){
                    $modal
                        ->select($this->_selector_modal_transfer_field_from, 0)
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_from)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_from)
                        ->select($this->_selector_modal_transfer_field_to, 0)
                        ->assertMissing($this->_selector_modal_transfer_meta_account_name_to)
                        ->assertMissing($this->_selector_modal_transfer_meta_last_digits_to)

                        ->assertDontSee($this->_label_account_type_meta_account_name)
                        ->assertDontSee($this->_label_account_type_meta_last_digits);
                })
                ->assertTransferModalSaveButtonIsDisabled();
        });
    }

    public function providerResetTransferModalFields(){
        return [
            // [$has_tags, $has_attachments]
            'standard fields'=>[false, false],
            'standard fields \w tags'=>[true, false],
            'standard fields \w attachments'=>[false, true],
            'standard fields \w tags & attachments'=>[true, true]
        ];
    }

    /**
     * @dataProvider providerResetTransferModalFields
     * @param $has_tags
     * @param $has_attachments
     *
     * @throws \Throwable
     */
    public function testResetTransferModalFields($has_tags, $has_attachments){
        $faker = FakerFactory::create();
        $all_account_types = $this->getApiAccountTypes();
        $account_types = $faker->randomElements($all_account_types, 2);

        $this->browse(function(Browser $browser) use ($account_types, $faker, $has_tags, $has_attachments){
            // get locale date string from browser
            $browser_locale_date = $this->getBrowserLocaleDate($browser);
            $browser_locale_date = $this->processLocaleDateForTyping($browser_locale_date);

            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openTransferModal()

                ->with($this->_selector_modal_transfer, function($modal) use ($browser_locale_date, $faker, $account_types){
                    $modal
                        // make sure all the fields are empty first
                        ->assertInputValue($this->_selector_modal_transfer_field_date, "")
                        ->assertInputValue($this->_selector_modal_transfer_field_value, "")
                        ->assertSelected($this->_selector_modal_transfer_field_from, "")
                        ->assertSelected($this->_selector_modal_transfer_field_to, "")
                        ->assertInputValue($this->_selector_modal_transfer_field_memo, "")
                        ->assertInputValue($this->_selector_modal_transfer_field_tags, "")
                        ->assertVisible($this->_selector_modal_transfer_field_upload)
                        ->assertMissing($this->_selector_modal_transfer_dropzone_upload_thumbnail)
                        // fill in fields
                        ->type($this->_selector_modal_transfer_field_date, $browser_locale_date)
                        ->type($this->_selector_modal_transfer_field_value, "123.45")
                        ->select($this->_selector_modal_transfer_field_from, $account_types[0]['id'])
                        ->select($this->_selector_modal_transfer_field_to, $account_types[1]['id'])
                        ->type($this->_selector_modal_transfer_field_memo, "Test transfer - reset");
                });

                if($has_tags){
                    // select tag at random and input the first character into the tags-input field
                    $tags = $this->getApiTags();
                    $tag = $faker->randomElement($tags);

                    $this->fillTagsInputUsingAutocomplete($browser, $tag['name']);
                }

                if($has_attachments){
                    $upload_file_path = storage_path($this->getRandomTestFileStoragePath());
                    $this->attachFile($browser, $upload_file_path);
                }

                $browser->with($this->_selector_modal_transfer, function($modal){
                    $modal->click($this->_selector_modal_transfer_btn_cancel);
                })
                ->assertMissing($this->_selector_modal_transfer)
                ->openTransferModal()
                ->with($this->_selector_modal_transfer, function($modal) use ($browser_locale_date, $account_types){
                    // make sure all the fields are empty after re-opening the transfer-modal
                    $modal
                        ->assertInputValue($this->_selector_modal_transfer_field_date, "")
                        ->assertInputValue($this->_selector_modal_transfer_field_value, "")
                        ->assertSelected($this->_selector_modal_transfer_field_from, "")
                        ->assertSelected($this->_selector_modal_transfer_field_to, "")
                        ->assertInputValue($this->_selector_modal_transfer_field_memo, "")
                        ->assertInputValue($this->_selector_modal_transfer_field_tags, "")
                        ->assertVisible($this->_selector_modal_transfer_field_upload)
                        ->with($this->_selector_modal_transfer_field_upload, function($upload_field){
                            $upload_field
                                ->assertMissing($this->_selector_modal_dropzone_upload_thumbnail)
                                ->assertSee($this->_label_file_upload);
                        });
                });
        });
    }

    public function providerSaveTransferEntry(){
        return [
            // [$is_to_account_external, $is_from_account_external, $has_tags, $has_attachments]
            'TO account is external'                            => [true,  false, false, false],
            'FROM account is external'                          => [false, true,  false, false],
            'neither account is external'                       => [false, false, false, false],
            'TO account is external w\ tags'                    => [true,  false, true,  false],
            'FROM account is external w\ tags'                  => [false, true,  true,  false],
            'neither account is external w\ tags'               => [false, false, true,  false],
            'TO account is external w\ attachments'             => [true,  false, false, true],
            'FROM account is external w\ attachments'           => [false, true,  false, true],
            'neither account is external w\ attachments'        => [false, false, false, true],
            'TO account is external w\ tags & attachments'      => [true,  false, true,  true],
            'FROM account is external w\ tags & attachments'    => [false, true,  true,  true],
            'neither account is external w\ tags & attachments' => [false, false, true,  true],
        ];
    }

    /**
     * @dataProvider providerSaveTransferEntry
     * @param bool $is_to_account_external
     * @param bool $is_from_account_external
     * @param bool $has_tags
     * @param bool $has_attachments
     *
     * @throws \Throwable
     */
    public function testSaveTransferEntry($is_to_account_external, $is_from_account_external, $has_tags, $has_attachments){
        $faker = FakerFactory::create();

        $this->browse(function(Browser $browser) use ($is_to_account_external, $is_from_account_external, $has_tags, $has_attachments, $faker){
            $all_account_types = $this->getApiAccountTypes();
            $account_types = $faker->randomElements($all_account_types, 2);
            $tag = '';
            if($has_tags){
                $all_tags = $this->getApiTags();
                $tag = $faker->randomElement($all_tags);
                $tag = $tag['name'];
            }

            // get locale date string from browser
            $browser_locale_date = $this->getBrowserLocaleDate($browser);

            // generate some test values
            $transfer_entry_data = [
                'date'=>$this->processLocaleDateForTyping($browser_locale_date),
                'memo'=>"Test transfer - save".($has_tags?" w/ tags":'').($has_attachments?" w/ attachments":'').' - '.$faker->uuid,
                'value'=>$faker->randomFloat(2, 0, 100),
                'from_account_type_id'=>($is_from_account_external ? EntryController::TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID : $account_types[0]['id']),
                'to_account_type_id'=>($is_to_account_external ? EntryController::TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID : $account_types[1]['id']),
                'tag'=>$tag,
                'attachment_path'=>storage_path($this->getRandomTestFileStoragePath()),
            ];

            $browser->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openTransferModal()
                ->with($this->_selector_modal_transfer, function($modal) use ($transfer_entry_data){
                    $modal->type($this->_selector_modal_transfer_field_date, $transfer_entry_data['date'])
                        ->type($this->_selector_modal_transfer_field_value, $transfer_entry_data['value'])
                        ->waitUntilMissing($this->_selector_modal_transfer_field_from_is_loading)
                        ->select($this->_selector_modal_transfer_field_from, $transfer_entry_data['from_account_type_id'])
                        ->waitUntilMissing($this->_selector_modal_transfer_field_to_is_loading)
                        ->select($this->_selector_modal_transfer_field_to, $transfer_entry_data['to_account_type_id'])
                        ->type($this->_selector_modal_transfer_field_memo, $transfer_entry_data['memo']);
                });

                if($has_tags){
                    $this->fillTagsInputUsingAutocomplete($browser, $transfer_entry_data['tag']);
                }

                if($has_attachments){
                    $this->attachFile($browser, $transfer_entry_data['attachment_path']);
                }

                $browser->with($this->_selector_modal_transfer, function($modal){
                    $modal->click($this->_selector_modal_transfer_btn_save);
                })
                ->waitForLoadingToStop()
                ->assertNotification(HomePage::NOTIFICATION_SUCCESS, $this->_label_notification_transfer_saved)
                ->assertMissing($this->_selector_modal_transfer);

            $entry_modal_date_input_value = date("Y-m-d", strtotime($browser_locale_date));
            if(!$is_from_account_external){
                $this->assertTransferEntrySaved(
                    $browser,
                    $this->_selector_table.' '.$this->_selector_table_unconfirmed_expense.'.is-transfer',
                    $transfer_entry_data,
                    $entry_modal_date_input_value,
                    'from_account_type_id',
                    $this->_label_expense_switch_expense,
                    $has_tags,
                    $has_attachments
                );
            }
            if(!$is_to_account_external){
                $this->assertTransferEntrySaved(
                    $browser,
                    $this->_selector_table.' '.$this->_selector_table_unconfirmed_income.'.is-transfer',
                    $transfer_entry_data,
                    $entry_modal_date_input_value,
                    'to_account_type_id',
                    $this->_label_expense_switch_income,
                    $has_tags,
                    $has_attachments
                );
            }
        });
    }

    /**
     * @param Browser $browser
     * @return string
     */
    private function getBrowserLocaleDate(Browser $browser){
        $browser_locale_date = $browser->script('return new Date().toLocaleDateString()');
        return $browser_locale_date[0];
    }

    /**
     * @param string $locale_date
     * @return string
     */
    private function processLocaleDateForTyping($locale_date){
        $locale_date_components = [];
        if(strpos($locale_date, '/') !== false){
            $locale_date_components = explode('/', $locale_date);
        }elseif(strpos($locale_date, '-') !== false){
            $locale_date_components = explode('-', $locale_date);
        }
        foreach($locale_date_components as $key=>$date_component){
            $locale_date_components[$key] = ($date_component < 10) ? '0'.$date_component : $date_component;
        }
        return implode('', $locale_date_components);
    }

    private function fillTagsInputUsingAutocomplete(Browser $browser, $tag){
        $browser->with($this->_selector_modal_transfer, function($modal) use ($tag){
            $first_char = substr($tag, 0, 1);
            $second_char = substr($tag, 1, 2);

            $modal
                ->waitUntilMissing($this->_selector_modal_transfer_field_tags_container_is_loading, HomePage::WAIT_SECONDS)
                ->keys($this->_selector_modal_transfer_field_tags, $first_char)
                ->keys($this->_selector_modal_transfer_field_tags, $second_char)
                ->waitFor($this->_selector_modal_tag_autocomplete_options)
                ->assertSee($tag)
                ->click($this->_selector_modal_tag_autocomplete_options);
        });
    }

    private function attachFile(Browser $browser, $attachment_file_path){
        $this->assertFileExists($attachment_file_path);
        $browser->with($this->_selector_modal_transfer, function($modal) use ($attachment_file_path){
            $modal
                ->assertVisible($this->_selector_modal_transfer_field_upload)
                ->attach($this->_selector_modal_transfer_dropzone_hidden_file_input, $attachment_file_path)
                ->waitFor($this->_selector_modal_transfer_field_upload.' '.$this->_selector_modal_dropzone_upload_thumbnail, HomePage::WAIT_SECONDS);
        })
        ->assertNotification(HomePage::NOTIFICATION_INFO, sprintf($this->_label_notification_file_upload_success, basename($attachment_file_path)));
    }

    /**
     * @param Browser $browser
     * @param string $table_row_selector
     * @param array $transfer_entry_data
     * @param string $entry_modal_date_input_value
     * @param string $entry_switch_expense_label
     * @param string $account_type_key
     * @param bool $has_tags
     * @param bool $has_attachments
     */
    public function assertTransferEntrySaved(Browser $browser, $table_row_selector, $transfer_entry_data, $entry_modal_date_input_value, $account_type_key, $entry_switch_expense_label, $has_tags, $has_attachments){
        if($has_tags){
            $table_row_selector .= '.has-tags';
        }
        if($has_attachments){
            $table_row_selector .= '.has-attachments';
        }
        $browser
            ->with($table_row_selector, function($table_row) use ($transfer_entry_data, $entry_modal_date_input_value, $has_tags, $has_attachments){
                $table_row
                    ->assertSee($entry_modal_date_input_value)
                    ->assertSee($transfer_entry_data['memo'])
                    ->assertSee($transfer_entry_data['value'])
                    ->assertVisible($this->_selector_table_row_transfer_checkbox.' '.$this->_selector_table_is_checked_checkbox);
                if($has_tags){
                    $table_row->assertSee($transfer_entry_data['tag']);
                }
                if($has_attachments){
                    $table_row->assertVisible($this->_selector_table_row_attachment_checkbox.' '.$this->_selector_table_is_checked_checkbox);
                }
            })
            ->openExistingEntryModal($table_row_selector)
            ->with($this->_selector_modal_entry, function($modal) use ($transfer_entry_data, $entry_modal_date_input_value, $entry_switch_expense_label, $account_type_key, $has_tags, $has_attachments){
                $modal
                    ->assertInputValue($this->_selector_modal_entry_field_date, $entry_modal_date_input_value)
                    ->assertInputValue($this->_selector_modal_entry_field_value, $transfer_entry_data['value'])
                    ->assertSelected($this->_selector_modal_entry_field_account_type, $transfer_entry_data[$account_type_key])
                    ->assertSee($this->_label_account_type_meta_account_name)
                    ->assertSee($this->_label_account_type_meta_last_digits)
                    ->assertInputValue($this->_selector_modal_entry_field_memo, $transfer_entry_data['memo'])
                    ->assertSee($entry_switch_expense_label);

                if($has_tags){
                    $modal->assertVisible($this->_selector_modal_transfer_field_tags);

                    $elements = $modal->driver->findElements(WebDriverBy::cssSelector($this->_selector_modal_transfer_field_tags));
                    $this->assertGreaterThan(0, count($elements), "tag \"".$transfer_entry_data['tag']."\" not present in entry modal");
                    $modal->assertSee($transfer_entry_data['tag']);
                }

                if($has_attachments){
                    $modal->assertVisible($this->_selector_modal_entry_existing_attachments);

                    $elements = $modal->driver->findElements(WebDriverBy::className('existing-attachment'));
                    $this->assertGreaterThan(0, count($elements));
                }

                $modal->click($this->_selector_modal_entry_btn_cancel);
            })
            ->assertMissing($this->_selector_modal_entry);
    }

}

