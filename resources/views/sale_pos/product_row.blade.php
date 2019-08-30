<tr class="product_row" data-row_index="{{$row_count}}">
	<td>
		{{$product->product_name}}
		<br/>
		{{$product->sub_sku}}@if(!empty($product->brand)), {{$product->brand}} @endif
		&nbsp;
		<input type="hidden" class="enable_sr_no" value="{{$product->enable_sr_no}}">
		<i class="fa fa-commenting cursor-pointer text-primary add-pos-row-description" title="@lang('lang_v1.add_description')" data-toggle="modal" data-target="#row_description_modal_{{$row_count}}"></i>

		<!-- Description modal start -->
		<div class="modal fade row_description_modal" id="row_description_modal_{{$row_count}}" tabindex="-1" role="dialog">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel">{{$product->product_name}} - {{$product->sub_sku}}</h4>
		      </div>
		      <div class="modal-body">
		      	<div class="form-group">
		      		<label>@lang('lang_v1.description')</label>
		      		@php
		      			$sell_line_note = '';
		      			if(!empty($product->sell_line_note)){
		      				$sell_line_note = $product->sell_line_note;
		      			}
		      		@endphp
		      		<textarea class="form-control" name="products[{{$row_count}}][sell_line_note]" rows="3">{{$sell_line_note}}</textarea>
		      		<p class="help-block">@lang('lang_v1.sell_line_description_help')</p>
		      	</div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
		      </div>
		    </div>
		  </div>
		</div>
		<!-- Description modal end -->
		@if(in_array('modifiers' , $enabled_modules))
			<div class="modifiers_html">
				@if(!empty($product->product_ms))
					@include('restaurant.product_modifier_set.modifier_for_product', array('edit_modifiers' => true, 'row_count' => $loop->index, 'product_ms' => $product->product_ms ) )
				@endif
			</div>
		@endif

	</td>

	<td>
		{{-- If edit then transaction sell lines will be present --}}
		@if(!empty($product->transaction_sell_lines_id))
			<input type="hidden" name="products[{{$row_count}}][transaction_sell_lines_id]" class="form-control" value="{{$product->transaction_sell_lines_id}}">
		@endif

		<input type="hidden" name="products[{{$row_count}}][product_id]" class="form-control product_id" value="{{$product->product_id}}">

		<input type="hidden" value="{{$product->variation_id}}" 
			name="products[{{$row_count}}][variation_id]" class="row_variation_id">

		<input type="hidden" value="{{$product->enable_stock}}" 
			name="products[{{$row_count}}][enable_stock]">
		
		@if(empty($product->quantity_ordered))
			@php
				$product->quantity_ordered = 1;
			@endphp
		@endif
		<div class="input-group input-number">
			<span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-down"><i class="fa fa-minus text-danger"></i></button></span>
		<input type="text" data-min="1" class="form-control pos_quantity input_number mousetrap" value="{{@num_format($product->quantity_ordered)}}" name="products[{{$row_count}}][quantity]" 
		@if($product->unit_allow_decimal == 1) data-decimal=1 @else data-decimal=0 @endif
		data-rule-required="true" data-msg-required="@lang('validation.custom-messages.this_field_is_required')" @if($product->enable_stock) data-c-rule-max="{{$product->qty_available}}" data-msg-max="@lang('validation.custom-messages.quantity_not_available', ['qty'=> $product->formatted_qty_available, 'unit' => $product->unit  ])" @endif >
		<span class="input-group-btn"><button type="button" class="btn btn-default btn-flat quantity-up"><i class="fa fa-plus text-success"></i></button></span>
		</div>
		{{$product->unit}}
		
	</td>
	<td>
		<input type="text" name="products[{{$row_count}}][unit_price]" class="form-control pos_unit_price input_number mousetrap" value="{{@num_format($product->default_sell_price)}}">
	</td>
	@php
		$hide_tax = 'hide';
        if(session()->get('business.enable_inline_tax') == 1){
            $hide_tax = '';
        }
        
		$tax_id = $product->tax_id;
		$unit_price_inc_tax = $product->sell_price_inc_tax;
		if($hide_tax == 'hide'){
			$tax_id = null;
			$unit_price_inc_tax = $product->default_sell_price;
		}
	@endphp
	<td class="{{$hide_tax}}">
		<input type="hidden" name="products[{{$row_count}}][item_tax]" class="form-control item_tax">
		
		{!! Form::select("products[$row_count][tax_id]", $tax_dropdown['tax_rates'], $tax_id, ['placeholder' => 'Select', 'class' => 'form-control tax_id'], $tax_dropdown['attributes']); !!}
	</td>
	<td class="{{$hide_tax}}">
		<input type="text" name="products[{{$row_count}}][unit_price_inc_tax]" class="form-control pos_unit_price_inc_tax input_number" value="{{@num_format($unit_price_inc_tax)}}">
	</td>
	<td>
		<input type="text" readonly name="products[{{$row_count}}][price]" class="form-control pos_line_total" value="{{@num_format($product->quantity_ordered*$unit_price_inc_tax )}}">
	</td>
	<td class="text-center">
		<i class="fa fa-trash pos_remove_row cursor-pointer" aria-hidden="true"></i>
	</td>
</tr>