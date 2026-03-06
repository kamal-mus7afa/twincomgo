@extends('errors.layout')

@section('animation')

<lottie-player
src="https://assets2.lottiefiles.com/packages/lf20_ydo1amjm.json"
background="transparent"
speed="1"
style="width:300px;height:300px"
loop
autoplay>
</lottie-player>

@endsection

@section('code','403')

@section('message','Kamu tidak memiliki izin untuk mengakses halaman ini.')