<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Models;
use App\Core\Base\BaseModel;
class PaymentTransaction extends BaseModel {
    protected $table = 'payment_transactions';
    protected $fillable = ['organization_id','subscription_id','provider','provider_transaction_id','order_id','amount','currency','status','payment_method','attempt_number','gateway_fee','provider_response','paid_at','failed_at','expired_at'];
    protected $casts = ['amount'=>'integer','gateway_fee'=>'integer','attempt_number'=>'integer','provider_response'=>'array','paid_at'=>'datetime','failed_at'=>'datetime','expired_at'=>'datetime','created_at'=>'datetime','updated_at'=>'datetime'];
    public function subscription() { return $this->belongsTo(Subscription::class); }
}