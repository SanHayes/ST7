<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectOrder extends BaseModel
{
    protected $table = "project_order";
    use HasFactory;
     protected $appends = [
        'account'
    ];
    protected $casts = [
        'sub_time' => 'datetime',
    ];
    
    
    public function getAccountAttribute()
    {
        return $this->belongsTo(Users::class,  'user_id', 'id')->value('email');
    }
    public function getProjectNameAttribute()
    {
        return $this->belongsTo(Project::class,  'project_id', 'id')->value('project_name');
    }
    public function getStatusAttribute(): string
    {
        $status='';
        if($this->attributes['status']==1){
            $status='交易中';
        }else if($this->attributes['status']==2){
            $status='申请退款';
        }else if($this->attributes['status']==3){
            $status='交易完成';
        }else if($this->attributes['status']==4){
            $status='交易退回';
        }
        return $status;
    }
}
