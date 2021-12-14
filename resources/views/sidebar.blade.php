<div class="col-md-4">
    <div class="card">
        <div class="card-header">Action</div>

        <div class="card-body">
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-block">All repositories</a>
            <a href="{{ route('add-repo') }}" class="btn btn-outline-primary btn-block"><i class="fa fa-plus"></i> Add New Repository</a>
            <a href="{{ route('build-repo') }}" class="btn btn-outline-success btn-block"><i class="fa fa-rocket"></i> Build Packages</a>
            <a href="{{ route('configure-whmcs') }}" class="btn btn-outline-success btn-block"><i class="fa fa-cogs"></i> Configure WHMCS</a>
            <a href="{{ route('configure') }}" class="btn btn-outline-success btn-block"><i class="fa fa-cogs"></i> Configure Package Manager</a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">Repositories</div>

        <div class="card-body">
            <a href="{{ route('auth.redirect', 'gitlab') }}" class="btn btn-outline-primary btn-block">Gitlab Connect</a>
            <a href="{{ route('auth.redirect', 'github') }}" class="btn btn-outline-primary btn-block">Github Connect</a>
            <a href="{{ route('auth.redirect', 'bitbucket') }}" class="btn btn-outline-primary btn-block">Bitbucket Connect</a>
        </div>
    </div>
</div>



