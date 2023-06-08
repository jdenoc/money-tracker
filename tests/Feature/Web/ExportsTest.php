<?php

namespace Tests\Feature\Web;

use App\Models\AccountType;
use App\Traits\EntryFilterKeys;
use App\Traits\ExportsHelper;
use App\Traits\Tests\GenerateFilterTestCases;
use Faker\Factory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\Feature\Api\ListEntriesBase;

class ExportsTest extends ListEntriesBase {
    use EntryFilterKeys;
    use ExportsHelper;
    use GenerateFilterTestCases;

    const URI = "/export";
    const DOWNLOAD_LOCATION_CONFIG = 'excel.temporary_files.local_path';

    public function tearDown(): void {
        $download_location = config(self::DOWNLOAD_LOCATION_CONFIG);
        foreach (File::files($download_location) as $file) {
            if (!Str::contains($file, 'gitignore')) {
                File::delete($file);
            }
        }
        parent::tearDown();
    }

    public function providerExportRequestMethodNotAllowed(): array {
        return [
            // Note: POST is the only valid request method
            'GET'=>['GET'],
            'PATCH'=>['PATCH'],
            'PUT'=>['PUT'],
            'DELETE'=>['DELETE']
        ];
    }

    /**
     * @dataProvider providerExportRequestMethodNotAllowed
     * @param string $method
     */
    public function testExportRequestMethodNotAllowed(string $method) {
        // GIVEN - see provider

        // WHEN
        $response = $this->json($method, self::URI);

        // THEN
        $status_code = $response->getStatusCode();
        $this->assertEquals(HttpStatus::HTTP_METHOD_NOT_ALLOWED, $status_code);
    }

    public function providerExportPostRequest(): array {
        return $this->generateFilterTestCases(fake(Factory::DEFAULT_LOCALE));   // manually setting local to bypass needing to access configs
    }

    /**
     * @dataProvider providerExportPostRequest
     * @param array $filter
     */
    public function testExportPostRequest(array $filter) {
        // GIVEN - see provider
        $total_entries_to_create = fake()->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE);
        $generated_account_type = AccountType::factory()->create(['account_id'=>$this->_generated_account->id]);
        $filter = $this->setTestSpecificFilters(fake(), $filter, $this->_generated_account, $this->_generated_tags);
        $this->batch_generate_entries($total_entries_to_create, $generated_account_type->id, $this->convert_filters_to_entry_components($filter));

        $filename = $this->pregenerateExportFilenameAtStartOfSecond();

        // WHEN
        $download_response = $this->postJson(self::URI, $filter);
        $data_response = $this->postJson('/api/entries', $filter);

        // THEN
        $download_response->assertStatus(HttpStatus::HTTP_OK);
        $download_response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $download_response->assertHeader('content-disposition', sprintf('attachment; filename=%s', $filename));

        // We don't need to do any validation on the data from POST /api/entries
        // That is handled in another test suite
        $data_response_as_array = $data_response->json();
        unset($data_response_as_array['count']);
        $data_response = collect($data_response_as_array);

        // this is where the downloaded file gets stored
        // does not have the correct filename because it is a cached name :(
        $download_location = config(self::DOWNLOAD_LOCATION_CONFIG);
        $this->assertDirectoryExists($download_location);
        $downloaded_file_path = $download_response->getFile()->getPathName();
        $this->assertFileExists($downloaded_file_path);
        $this->assertStringContainsString($download_location, $downloaded_file_path,
            sprintf("Downloaded file [%s] is not in the expected location [%s]", $downloaded_file_path, $download_location)
        );
        $file_handle = fopen($downloaded_file_path, 'r');

        // assert header line of file
        $header = fgetcsv($file_handle);
        $this->assertEquals($this->getCsvHeaderLine(), $header);

        // assert contents
        while ($line = fgetcsv($file_handle)) {
            $expected_entry = $data_response->firstWhere('id', $line[0]);

            $this->assertEquals($expected_entry['entry_date'], $line[1]);
            $this->assertEquals($expected_entry['memo'], $line[2]);
            $expected_income = $expected_entry['expense'] ? '' : $expected_entry['entry_value'];
            $expected_expense = $expected_entry['expense'] ? $expected_entry['entry_value'] : '';
            $this->assertEquals($expected_income, $line[3]);    // Income
            $this->assertEquals($expected_expense, $line[4]);   // Expense
            $this->assertEquals($expected_entry['account_type_id'], $line[5]);
            $this->assertEquals($expected_entry['has_attachments'], filter_var($line[6], FILTER_VALIDATE_BOOL)); // Attachment
            $this->assertEquals($expected_entry['is_transfer'], filter_var($line[7], FILTER_VALIDATE_BOOL));     // Transfer
            $actual_tags = empty($line[8]) ? [] : json_decode($line[8], true);     // Tags
            $this->assertEquals($expected_entry['tags'], $actual_tags);
        }
        fclose($file_handle);
    }

    private function pregenerateExportFilenameAtStartOfSecond(): string {
        // without this do-while loop we run the risk of generating
        // a filename that is off by 1 seconds from the download
        do {
            $microtime = explode(' ', microtime())[0];
        } while ($microtime > 0.25);
        return $this->generateExportFilename();
    }

}
