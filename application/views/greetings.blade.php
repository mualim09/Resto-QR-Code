<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <link rel="stylesheet" href="{{ base_url() }}assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ base_url() }}assets/css/costum.css">
    <title>Resto Bumi</title>
</head>
<body>
    <div class="container text-center">
        <div class="card text-white bg-success mb-3 mx-auto" style="max-width: 18rem;">
            <div class="card-body text-center">
                <h5 class="card-title">Thank You</h5>
                <p class="card-text">for enjoying our meal</p>
                <p class="card-text">Come again next time ! </p>
            </div>
        </div>

        <a href="print/{{$transID}}" class="btn btn-primary btn-lg btn-block mt-5">Invoice</a>
    </div>
</body>
</html>