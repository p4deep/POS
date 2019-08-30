<div class="row">
	<div class="col-md-10 col-md-offset-1 col-xs-12">
		<div class="table-responsive">
			<table class="table table-condensed bg-gray">
				<tr>
					<th>SKU</th>
					<th>Variation</th>
					<th>@lang('report.current_stock')</th>
					<th>@lang('report.total_unit_sold')</th>
				</tr>
				@foreach( $product_details as $details )
					<tr>
						<td>{{ $details->sub_sku}}</td>
						<td>
							{{ $details->product . '-' . $details->product_variation . 
							'-' .  $details->variation }}
						</td>
						<td>
							@if($details->stock)
								{{ $details->stock }}
							@else
							 0
							@endif
						</td>
						<td>
							@if($details->total_sold)
								{{ $details->total_sold }}
							@else
							 0
							@endif
						</td>
					</tr>
				@endforeach
			</table>
		</div>
	</div>
</div>