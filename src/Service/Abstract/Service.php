<?php
declare(strict_types=1);
namespace App\Service\Abstract;

abstract class Service {

    protected array $serviceArray = [];
    protected bool $eager = true;

    /** This method will turn on/off the autofetch of FK objects */
    public function setFetchFKStatus(bool $eager) {
    
        $this->eager = $eager;
    }
    
    /** Fetches the FK objects if the Service is in EAGER mode */
    protected function fetchFKs(array $subjects) {

        if ($this->eager) {

            foreach ($subjects as $sub) {
                
                $sub->refresh($this->serviceArray);
            }
        }
    }
}