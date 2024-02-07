<!-- View: glare-check.blade.php -->
<form action="{{ route('check-glare') }}" method="post" enctype="multipart/form-data">
              @csrf
            <input id="file-upload" type="file" name="photo" accept="image/*" />
<!-- <form method="POST" action="/check-glare">
  @csrf
  <input type="file" name="photo"> -->
  
  <button type="submit">Check for Glare</button>
</form>


