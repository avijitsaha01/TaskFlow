@if(session('success'))
    <input type="hidden" id="flashMessage" value="{{ session('success') }}">
@elseif(session('error'))
    <input type="hidden" id="flashMessage" value="{{ session('error') }}">
@endif

<div class="toast" id="toast"></div>
