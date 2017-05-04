<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sku', 255); // Unsure of SKU spec
            $table->string('plu', 255);
            $table->string('name', 65535);
            $table->string('size', 100);
            $table->integer('size_sort_id');
            $table->timestamps();

            // Add indexes
            $table->primary('id');
            $table->unique('sku');
            $table->index('plu');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
