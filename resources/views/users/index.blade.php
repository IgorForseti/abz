<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <table class="table" id="users">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Photo</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Position</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <th scope="row">{{$user->id}}</th>
                    <th scope="row"><img src="@if(strpos($user->photo,'http') !== false){{$user->photo}} @else{{asset('/image/logo').'/' . $user->photo}}@endif"></th>
                    <th scope="row">{{$user->name}}</th>
                    <th scope="row">{{$user->email}}</th>
                    <th scope="row">{{$user->phone}}</th>
                    <th scope="row">{{$user->position->name}}</th>
                </tr>
            @endforeach
            </tbody>
        </table>

        <button type="button" data-offset="{{$users->currentPage()*6}}" class="btn btn-primary" @if($users->currentPage() == $users->lastPage()) hidden @endif
            onclick="showMore(this);return false">Показать еще</button>

        <hr>

        <div class="col-md-12">
            <nav aria-label="Page navigation">
                {{ $users->links()}}
            </nav>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    function showMore(button)
    {
        let curPage = +document.querySelectorAll('li.page-item.active>span')[0].textContent,
            count = 6,
            offset = +$(button).attr('data-offset'),
            tbody = $('table#users tbody'),
            tr = '';

        $.get( "/api/v1/users?offset=" + offset + "&count=" + count, function(data) {
            console.log(data);
            if(data.users.length) {
                $.each(data.users, function (index, value) {
                    tr += '<tr>' +
                        '<th scope="row">' + value.id + '</th>' +
                        '<th scope="row"><img src="' + value.photo + '"></th>' +
                        '<th scope="row">' + value.name + '</th>' +
                        '<th scope="row">' + value.email + '</th>' +
                        '<th scope="row">' + value.phone + '</th>' +
                        '<th scope="row">' + value.position + '</th>' +
                        '</tr>';
                });
                tbody.append(tr);
            }

            $(button).attr('data-offset',offset + count);

            if (data.links.next_url === null) {
                $('button').attr('hidden',true);
            }
        });

    }
</script>
</body>
</html>
