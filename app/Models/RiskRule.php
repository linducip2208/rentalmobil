<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RiskRule extends Model { protected $fillable=['name','field','operator','comparison_value','score_delta','action','priority','is_active']; protected function casts():array{return['score_delta'=>'integer','priority'=>'integer','is_active'=>'boolean'];} }
