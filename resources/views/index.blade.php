@extends('inbox::layouts.app')
@section('title', 'الرسائل')
@section('content')
	@if($threads->count())
		<div class="list-group">
			@foreach($threads as $thread)
				@include('inbox::loop.thread')
			@endforeach
		</div>
	@else
		<div class="p-5 border">
			<h3 class="text-center font-weight-bold">There is no messages</h3>
		</div>
	@endif
@endsection

@section('pagination')
	@if($threads->hasPages())
		{!! $threads->render() !!}
	@endif
@endsection
