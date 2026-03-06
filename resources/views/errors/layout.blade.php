<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Error @yield('code')</title>

<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script
  src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.9.3/dist/dotlottie-wc.js"
  type="module"
></script>

<style>
body{
    margin:0;
    height:100vh;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    font-family:Arial, sans-serif;
    background:#0f172a;
    color:white;
    text-align:center;
}

h1{
    font-size:80px;
    margin:10px 0;
}

p{
    opacity:0.8;
}

a{
    margin-top:20px;
    padding:10px 20px;
    background:#38bdf8;
    color:black;
    text-decoration:none;
    border-radius:8px;
}
</style>

</head>

<body>

@yield('animation')

<h1>@yield('code')</h1>
<p>@yield('message')</p>

<a href="/">Kembali ke Home</a>

</body>
</html>