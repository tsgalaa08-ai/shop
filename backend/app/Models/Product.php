<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Product extends Model { use HasFactory; protected $fillable = ['category_id', 'name', 'slug', 'sku', 'description', 'price', 'sale_price', 'stock', 'image', 'status']; protected function casts(): array { return ['price' => 'decimal:2', 'sale_price' => 'decimal:2', 'status' => 'boolean']; } public function category() { return $this->belongsTo(Category::class); } public function getEffectivePriceAttribute(): string { return $this->sale_price ?? $this->price; } }