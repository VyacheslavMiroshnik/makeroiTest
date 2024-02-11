<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>
<main>
    <div class="container">
        <div class="form-wrap d-flex align-items-center justify-content-center">
            <form class="form p-5 shadow-lg rounded" action={{route('leads')}} method="POST">
                @csrf
                @method('POST')
                  <a class="text-decoration-none fs-4 d-block text-center mb-3 text-dark">
                    Сделка</a>
                <div class="mb-3">
                    <label for="leadName" class="form-label">Название сделки</label>
                    <input type="text" class="form-control" id="leadName" name="lead" >
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Бюджет</label>
                    <input type="number" class="form-control" id="price" name="price">
                </div>
                <div class="mb-3">
                    <label for="cost" class="form-label">Себестоимость</label>
                    <input type="number" class="form-control" id="cost" name="costPrice">
                </div>
                <button type="submit" class="btn btn-primary">Добавить</button>
            </form>
        </div>
    </div>
</main>
</body>
</html>
