<?php namespace Mohsin\Locality\Models;

use Model;

/**
 * Locality Model
 */
class Locality extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table associated with the model
     */
    public $table = 'mohsin_locality_localities';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = ['name', 'code', 'is_enabled'];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'name' => 'required|unique:mohsin_locality_localities,name',
        'code' => 'required|unique:mohsin_locality_localities,code'
    ];

    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'state' => ['RainLab\Location\Models\State']
    ];
}
