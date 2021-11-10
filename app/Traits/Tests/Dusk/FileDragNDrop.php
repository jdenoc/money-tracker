<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\StorageTestFiles;
use Laravel\Dusk\Browser;
use Illuminate\Support\Str;

trait FileDragNDrop {

    use StorageTestFiles;

    private static $SELECTOR_FILE_DRAG_N_DROP_UPLOAD_NODE = '.filepond--list .filepond--item:first-child';
    private static $SELECTOR_FILE_DRAG_N_DROP_INPUT = "input[type='file']";
    private static $SELECTOR_FILE_DRAG_N_DROP_BUTTON_REMOVE_UPLOADED = ".filepond--file-action-button.filepond--action-revert-item-processing";

    private static $ATTRIBUTE_UPLOAD_NODE_STATE = "data-filepond-item-state";
    private static $UPLOAD_NODE_STATE_DEFAULT = 'processing';
    private static $UPLOAD_NODE_STATE_ERROR = 'error';
    private static $UPLOAD_NODE_STATE_INVALID = 'invalid';
    private static $UPLOAD_NODE_STATE_COMPLETE = 'processing-complete';

    /**
     * @param Browser $modal
     * @param string  $selector_upload_file_container
     * @param string  $upload_file_path
     * @param string  $final_upload_state
     */
    protected function uploadAttachmentUsingDragNDrop(Browser $modal, string $selector_upload_file_container, string $upload_file_path, string $final_upload_state){
        $this->assertContains($final_upload_state, [self::$UPLOAD_NODE_STATE_COMPLETE, self::$UPLOAD_NODE_STATE_INVALID, self::$UPLOAD_NODE_STATE_ERROR, self::$UPLOAD_NODE_STATE_DEFAULT]);

        $this->assertFileExists($upload_file_path);

        $modal
            ->assertVisible($selector_upload_file_container)
            ->within($selector_upload_file_container, function(Browser $drag_n_drop) use ($upload_file_path, $final_upload_state){
                $drag_n_drop
                    // upload file
                    ->attach(self::$SELECTOR_FILE_DRAG_N_DROP_INPUT, $upload_file_path)
                    // wait for upload node to appear
                    ->waitFor(self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_NODE, self::$WAIT_SECONDS)
                    ->within(self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_NODE, function(Browser $upload_node) use ($upload_file_path, $final_upload_state){
                        // confirm file name in upload node
                        $upload_node->assertSeeIn('', basename($upload_file_path));
                        // wait for upload node state to change from default
                        $upload_node->waitUsing(self::$WAIT_SECONDS,100, function() use($upload_node){
                            $upload_item_state = $upload_node->attribute('', self::$ATTRIBUTE_UPLOAD_NODE_STATE);
                            return !Str::endsWith($upload_item_state, self::$UPLOAD_NODE_STATE_DEFAULT);
                        }, 'Waited '.self::$WAIT_SECONDS.' seconds for attribute to change');
                        // confirm that upload processing state completed
                        $upload_item_state = $upload_node->attribute('', self::$ATTRIBUTE_UPLOAD_NODE_STATE);
                        $this->assertStringContainsString($final_upload_state, $upload_item_state);
                    })
                    ->pause(self::$WAIT_TENTH_SECONDS_IN_MILLISECONDS);
            });
    }

    protected function removeUploadedAttachmentFromDragNDrop(Browser $modal){
        $modal
            ->within(self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_NODE, function(Browser $drag_n_drop){
                // this will process is only valid on a successful upload
                $drag_n_drop
                    ->assertVisible(self::$SELECTOR_FILE_DRAG_N_DROP_BUTTON_REMOVE_UPLOADED)
                    ->click(self::$SELECTOR_FILE_DRAG_N_DROP_BUTTON_REMOVE_UPLOADED);
            })
            ->pause(self::$WAIT_HALF_SECOND_IN_MILLISECONDS)   // give the element some time to disappear
            ->assertMissing(self::$SELECTOR_FILE_DRAG_N_DROP_UPLOAD_NODE);
    }

    /**
     * @param string $file_name
     * @param int $file_size    size of file to be created in bytes
     */
    private function generateDummyFile(string $file_name, int $file_size){
        $fp = fopen($file_name, 'w');
        fseek($fp, $file_size-1, SEEK_CUR);
        fwrite($fp, 'z');
        fclose($fp);
    }

    /**
     * @return string
     */
    private function getTestDummyFilename(): string{
        return \Storage::path(self::$storage_path.$this->getName(false).'.txt');
    }

}
