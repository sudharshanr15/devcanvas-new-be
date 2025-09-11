<form action="" method="post">
    @csrf
    <div>
        <label for="">Database name: </label>
        <input type="text" name="name" required>
    </div>

    <button type="submit">submit</button>
</form>

<h1>List of Databases</h1>
<ul>
    @foreach ($databases as $item)
        <li>{{ $item->name }}</li>
    @endforeach
</ul>