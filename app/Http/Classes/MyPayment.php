<?php

namespace App\Http\Classes;

use \PagSeguro\Library;

class MyPayment
{
    private $checkout;
    private $items;
    private $costumerName;
    private $costumerPhone;
    private $costumerEmail;
    private $notificationURL;

    public function __construct($clientID)
    {
        \PagSeguro\Library::initialize();
        \PagSeguro\Library::cmsVersion()->setName("Fadergs Hotel")->setRelease("1.0.0");
        \PagSeguro\Library::moduleVersion()->setName("Fadergs Hotel")->setRelease("1.0.0");

        \PagSeguro\Configuration\Configure::setEnvironment(env('PS_ENVIROMENT'));//production or sandbox
        \PagSeguro\Configuration\Configure::setAccountCredentials(
            env('PS_ACC'),
            env('PS_TOKEN')
        );
        \PagSeguro\Configuration\Configure::setCharset('UTF-8');// UTF-8 or ISO-8859-1
        touch(storage_path('logs/ps/'  .$clientID . "_" . date('now') . '.log'));
        \PagSeguro\Configuration\Configure::setLog(true,
            storage_path('logs/ps/'  .$clientID . "_" . date('now') . '.log')
        );

        $this->checkout = new \PagSeguro\Domains\Requests\Payment();
    }

    public function generetePSURL()
    {
        if(!isset($this->costumerName, $this->costumerPhone,$this->costumerEmail, $this->items,$this->notificationURL)){
            return false;
        }

        $this->checkout->setCurrency(env('PS_CURRENCY'));
        $this->checkout->setRedirectUrl(env('PS_REDIRECT_URL'));
        $this->checkout->setNotificationUrl($this->notificationURL);

        if(is_array($this->items))
        {
            $counter = 1;
            foreach ($this->items as $item)
            {
                if(isset($item['desc'], $item['days'], $item['value'])){
                    $this->checkout->addItems()->withParameters(
                        str_pad($counter,4,'0',STR_PAD_LEFT),
                        $item['desc'],
                        $item['days'],
                        $item['value']
                    );
                    $counter++;
                }
            }
        }

        $this->checkout->setSender()->setName($this->costumerName);
        $this->checkout->setSender()->setEmail($this->costumerEmail);
        $this->checkout->setSender()->setPhone()->withParameters(
            substr($this->costumerPhone, 0 ,2),
            substr($this->costumerPhone, 2)
        );

        try {
            $result = $this->checkout->register(
                \PagSeguro\Configuration\Configure::getAccountCredentials()
            );
            return $result;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function __get($property){
        if(property_exists($this,$property)){
            return $this->$property;
        }
        return false;
    }

    public function __isset($property)
    {
        return isset($property);
    }

    public function __set($property, $value)
    {
        switch ($property){
            case 'items':
                if(!isset($value['desc'], $value['days'], $value['value'])){
                    return false;
                }
                $this->$property[] = $value;
                break;
            case 'checkout':
                return false;
                break;
            default:
                $this->$property = $value;
                break;
        }
        return true;
    }


}
