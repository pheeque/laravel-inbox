<a href="{{ route('inbox.show', $thread->id) }}" class="list-group-item {{ !$thread->isUnread() ? 'read' : '' }}">
	<div class="checkbox d-inline-block">
		<label>
			<input type="checkbox">
		</label>
	</div>
	<i class="far fa-star"></i>
	@if($thread->isUnread())
		<span class="badge badge-success">New</span>
	@endif

	<span class="d-inline-block mr-5">{{ $thread->user->username }}</span>
	<span class="badge badge-danger">{{ $thread->messages->count() }}</span>
	<span>{{ $thread->subject }}</span>
	<small class="text-muted">- {{ str_limit($thread->lastMessage()->body, 50) }}</small>
	<span class="float-right badge badge-secondary ml-2">{{ $thread->updated_at->diffForHumans() }}</span>
	{{--<a href="{{ route('inbox.destroy', $thread->id) }}"><span class="lil-trash"></span></a>--}}
	{{--<span class="float-right fa fa-paperclip"></span>--}}
</a>
