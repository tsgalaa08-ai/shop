<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Order extends Model { use HasFactory; protected $fillable = ['order_number', 'user_id', 'customer_name', 'phone', 'email', 'district', 'khoroo', 'address', 'note', 'subtotal', 'delivery_fee', 'total', 'status', 'payment_status']; protected function casts(): array { return ['subtotal' => 'decimal:2', 'delivery_fee' => 'decimal:2', 'total' => 'decimal:2']; } public function items() { return $this->hasMany(OrderItem::class); } public function payments() { return $this->hasMany(Payment::class); } }