<?php

namespace Goodoneuz\PayUz;

use Goodoneuz\PayUz\Http\Classes\Click\Click;
use Goodoneuz\PayUz\Http\Classes\Payme\Payme;
use Goodoneuz\PayUz\Http\Classes\PaymentException;
use Goodoneuz\PayUz\Models\PaymentSystem;
use Goodoneuz\PayUz\Models\Transaction;
use Illuminate\Support\Facades\View;

class PayUz
{

    protected $driverClass = null;

    /**
     * PayUz constructor.
     */
    public function __construct()
    {
    }



    /**
     * Select payment driver
     * @param null $driver
     * @return $this
     */
    public function driver($driver = null){
        switch ($driver){
            case PaymentSystem::PAYME:
                $this->driverClass = Payme::class;
                break;
            case PaymentSystem::CLICK:
                $this->driverClass = Click::class;
                break;
        }
        return $this;
    }

    /**
     * Redirect to payment system
     * @param $model
     * @param $amount
     * @param int $currency_code
     * @return PayUz
     * @throws \Exception
     */
    public function redirect($model, $amount, $currency_code = 860){
        $this->validateDriver();
        $invoice = $this->createInvoice($model, $amount, $currency_code);
        $params = $this->driverClass::getRedirectParams($model, $amount);
        echo view('pay-uz::redirect.redirect',compact('params'));
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function handle(){
        $this->validateDriver();
        try{
            (new $this->driverClass)->run();
        }catch(PaymentException $e){
            $e->response();
        }

        return $this;
    }

    /**
     * @param $model
     * @param $amount
     * @param $currency_code
     * @throws \Exception
     */
    public function validateModel($model, $amount, $currency_code){
        if (is_null($model))
            throw new \Exception('Modal can\'t be null');
        if (is_null($amount) || $amount == 0)
            throw new \Exception('Amount can\'t be null or 0');
        if (is_null($currency_code))
            throw new \Exception('Currency code can\'t be null');
    }

    /**
     * @throws \Exception
     */
    public function validateDriver(){
        if (is_null($this->driverClass))
            throw new \Exception('Driver not selected');
    }
}
