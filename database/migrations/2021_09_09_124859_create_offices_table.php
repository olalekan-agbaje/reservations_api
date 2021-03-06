<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('featured_image_id')->index()->nullable();
            $table->string('title');
            $table->text('description');
            $table->decimal('lat', 11, 8);
            $table->decimal('lng', 11, 8);
            $table->text('address_line1');
            $table->text('address_line2')->nullable();
            $table->tinyInteger('approval_status')->default(1);
            $table->boolean('hidden')->default(false);
            $table->decimal('price_per_day', 10, 2);
            $table->integer('monthly_discount')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('office_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('office_id')->index();
            $table->foreignId('tag_id')->index();
            $table->unique(['office_id', 'tag_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offices');
    }
}
