<?php namespace Mohsin\Locality\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateLocalitiesTable Migration
 */
class CreateLocalitiesTable extends Migration
{
    public function up()
    {
        Schema::create('mohsin_locality_localities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('state_id')->unsigned()->nullable()->index();
            $table->string('name')->index()->unique();
            $table->string('code')->unique();
            $table->boolean('is_enabled')->default(true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mohsin_locality_localities');
    }
}
