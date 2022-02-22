<?php

namespace Tests\Feature\Web;

use App\Attachment;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Ramsey\Uuid\Uuid;

class AttachmentsDisplayTest extends TestCase {

    use WithFaker;

    const WEB_ATTACHMENT_URI = "/attachment/%s";
    const ATTACHMENT_PARAMETER_UUID = 'uuid';
    const ATTACHMENT_PARAMETER_FILENAME = 'name';

    /**
     * @var string[]
     */
    private $_valid_file_types = [
        'pdf',
        'jpeg',
        'jpg',
        'png',
        'gif',
        'txt',
    ];

    public function testInvalidUuid(){
        // GIVEN
        $invalid_uuid = $this->faker->word();   // a word will never be a valid UUID

        // WHEN
        $response = $this->get(sprintf(self::WEB_ATTACHMENT_URI, $invalid_uuid));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
    }

    public function testNoDatabaseRecord(){
        //GIVEN - no attachment database record
        $uuid = $this->generateValidUuid();

        //WHEN
        $response = $this->get(sprintf(self::WEB_ATTACHMENT_URI, $uuid));

        //THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
    }

    public function testFileNotOnDisk(){
        //GIVEN - file does NOT exist on disk
        $generated_attachment = factory(Attachment::class)->create([self::ATTACHMENT_PARAMETER_UUID=>$this->generateValidUuid()]);

        //WHEN
        $response = $this->get(sprintf(self::WEB_ATTACHMENT_URI, $generated_attachment->uuid));

        //THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
    }

    public function testInvalidAttachmentFileType(){
        //GIVEN
        do{
            // file extension should NOT be in the approved list
            $file_ext = $this->faker->fileExtension();
        }while(in_array($file_ext, $this->_valid_file_types));
        $generated_attachment = factory(Attachment::class)->create([
            self::ATTACHMENT_PARAMETER_UUID => $this->generateValidUuid(),
            self::ATTACHMENT_PARAMETER_FILENAME => $this->faker->word().'.'.$file_ext
        ]);
        // make sure file exists on disk by generating a dummy file.
        $generated_attachment->storage_store($this->faker->sentence());

        //WHEN
        $response = $this->get(sprintf(self::WEB_ATTACHMENT_URI, $generated_attachment->uuid));

        //THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

    public function testViewingAValidAttachment(){
        //GIVEN
        $generated_attachment = factory(Attachment::class)->create([
            self::ATTACHMENT_PARAMETER_UUID => $this->generateValidUuid(),
            self::ATTACHMENT_PARAMETER_FILENAME => $this->faker->word().'.'.$this->faker->randomElement($this->_valid_file_types)
        ]);
        // make sure file exists on disk by generating a dummy file.
        $generated_attachment->storage_store($this->faker->sentence());

        //WHEN
        $response = $this->get(sprintf(self::WEB_ATTACHMENT_URI, $generated_attachment->uuid));

        //THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK);
    }

    private function generateValidUuid():string{
        do{
            $valid_uuid = $this->faker->uuid();
        }while(!Uuid::isValid($valid_uuid));
        return $valid_uuid;
    }

}