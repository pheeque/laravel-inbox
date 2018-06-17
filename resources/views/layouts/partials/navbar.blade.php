<nav class="navbar navbar-expand-lg navbar-light bg-light mb-5">
	<div class="container-fluid">
		<a class="navbar-brand" href="{{ route(config('inbox.route.name') . 'inbox.index') }}">Liliom Inbox</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor01"
		        aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarColor01">
			<form class="form-inline">
				<input class="form-control mr-sm-2" type="search" placeholder="Search..">
				<button class="btn btn-outline-info my-2 my-sm-0" type="submit">Search</button>
			</form>

			<ul class="navbar-nav ml-auto">
				<li class="nav-item dropdown">
					<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
						{{ auth()->user()->name }}
					</a>
					<div class="dropdown-menu dropdown-menu-right">
						<a class="dropdown-item" href="#">Logout</a>
					</div>
				</li>
			</ul>
		</div>
	</div>
</nav>