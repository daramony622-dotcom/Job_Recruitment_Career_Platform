<?php

namespace Tests\Unit\Unit;

use App\Services\CloudinaryService;
use Tests\TestCase;

class CloudinaryServiceTest extends TestCase
{
    public function test_cloudinary_service_can_be_instantiated_with_config(): void
    {
        config([
            'services.cloudinary.cloud_name' => 'd9gdutgs',
            'services.cloudinary.api_key'    => '459319355457353',
            'services.cloudinary.api_secret' => '-8Hiaf9nmTkKdI0qs7s6KVecMiA',
        ]);

        $service = new CloudinaryService();

        $this->assertInstanceOf(CloudinaryService::class, $service);
    }
}

