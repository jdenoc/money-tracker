<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Faker\Factory as FakerFactory;
use App\Console\Commands\AppVersion as AppVersionCommand;
use Illuminate\Foundation\Testing\WithoutMiddleware;

/**
 * Class AppVersionTest
 * Code in this test class has been inspired by this StackOverflow post: https://stackoverflow.com/a/37469635
 *
 * @package Tests\Feature\Console
 */
class AppVersionTest extends TestCase {

    /**
     * @var string
     */
    private $_command = "app:version";

    /**
     * @var string
     */
    private $_test_version;

    public function setUp(){
        parent::setUp();
        $faker = FakerFactory::create();
        $this->_test_version = $faker->randomDigitNotNull.'.'.$faker->randomDigitNotNull.'.'.$faker->randomDigitNotNull.'-test-'.$faker->word;
    }

    public function testSettingAppVersionViaArgument(){
        Artisan::call($this->_command, [AppVersionCommand::ARG_NAME => $this->_test_version]);

        $result_as_text = trim(Artisan::output());
        $this->assertEquals(
            sprintf(AppVersionCommand::INFO_STRING_SET_VERSION, $this->_test_version),
            $result_as_text,
            "command output invalid"
        );
        $this->assertEquals(
            $this->_test_version,
            config(AppVersionCommand::CONFIG_PARAM),
            "config value not updated"
        );
    }

    public function testSettingAppVersionViaGitOption(){
        $git_version = $this->obtainVersionFromGit();
        Artisan::call($this->_command, ['--git' => true]);

        $result_as_text = trim(Artisan::output());
        $this->assertEquals(
            sprintf(AppVersionCommand::INFO_STRING_SET_VERSION, $git_version),
            $result_as_text,
            "command output invalid"
        );
        $this->assertEquals(
            $git_version,
            config(AppVersionCommand::CONFIG_PARAM),
            "config value not updated"
        );
    }

    public function testSettingAppVersionViaGitOptionAndArgument(){
        $git_version = $this->obtainVersionFromGit();
        Artisan::call($this->_command, [
            AppVersionCommand::ARG_NAME => $this->_test_version,
            '--git' => true
        ]);

        $result_as_text = trim(Artisan::output());
        $this->assertEquals(
            sprintf(AppVersionCommand::INFO_STRING_SET_VERSION, $git_version),
            $result_as_text,
            "command output invalid"
        );
        $this->assertEquals(
            $git_version,
            config(AppVersionCommand::CONFIG_PARAM),
            "config value not updated"
        );
    }

    public function testGettingAppVersion(){
        config([AppVersionCommand::CONFIG_PARAM=>$this->_test_version]);
        Artisan::call($this->_command);

        $result_as_text = trim(Artisan::output());
        $this->assertEquals(sprintf(AppVersionCommand::INFO_STRING_GET_VERSION, $this->_test_version), $result_as_text);
    }

    /**
     * Taken from App\Console\Commands\AppVersion->obtainVersionFromGit()
     * @return string
     */
    private function obtainVersionFromGit(){
        $git_version = '';
        exec('git describe', $git_version);
        $git_version = $git_version[0];
        return $git_version;
    }
}
