<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="theme-color" content="#000000" />
    <title>E-Presensi GeoLocation</title>
    <meta name="description" content="Mobilekit HTML Mobile UI Kit" />
    <meta name="keywords" content="bootstrap 4, mobile template, cordova, phonegap, mobile, html" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}" sizes="32x32" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/icon/192x192.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
    {{-- <link rel="manifest" href="__manifest.json" /> --}}
</head>

<body class="bg-white">
    <!-- loader -->
    <div id="loader">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
    <!-- * loader -->

    <!-- App Capsule -->
    <div id="appCapsule" class="pt-0">
        <div class="login-form mt-1">
            <div class="section">
                <img src="{{ asset('assets/img/login/login.jpg') }}" alt="image" class="form-image" />
            </div>
            <div class="section mt-1">
                <h1>e-presensi</h1>
                <h4>Silahkan Login</h4>
            </div>
            <div class="section mt-1 mb-5">
                @php
                    $messagewarning = Session()->get('warning');
                @endphp

                @if (Session()->get('warning'))
                    <div class="alert alert-outline-warning">
                        {{ $messagewarning }}
                    </div>
                @endif

                <form action="/proseslogin" method="POST" id="login-form">
    @csrf
    <div class="form-group boxed">
        <div class="input-wrapper">
            <input type="text" class="form-control" id="nik" placeholder="NIK" name="nik" />
        </div>
    </div>

    <div class="form-group boxed">
        <div class="input-wrapper">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" />
        </div>
    </div>

    <input type="hidden" name="user_location" id="user_location">

    <div class="form-button-group">
        <button type="submit" class="btn btn-primary btn-block btn-lg">
            Log in
        </button>
    </div>
</form>
            </div>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

    <!-- * App Capsule -->

    <!-- ///////////// Js Files ////////////////////  -->
    <!-- Jquery -->
    <script src="{{ asset('assets/js/lib/jquery-3.4.1.min.js') }}"></script>
    <!-- Bootstrap-->
    <script src="{{ asset('assets/js/lib/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/bootstrap.min.js') }}"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@5.0.0/dist/ionicons/ionicons.js"></script>
    <!-- Owl Carousel -->
    <script src="{{ asset('assets/js/plugins/owl-carousel/owl.carousel.min.js') }}"></script>
    <!-- jQuery Circle Progress -->
    <script src="{{ asset('assets/js/plugins/jquery-circle-progress/circle-progress.min.js') }}"></script>
    <!-- Base Js File -->
    <script src="{{ asset('assets/js/base.js') }}"></script>
    <!-- Add Leaflet library and geolocation script -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Menampilkan peta menggunakan Leaflet
        var map = L.map('map').setView([-6.902593, -252.201328], 30); // Ganti dengan koordinat pusat peta yang diinginkan
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Mendapatkan lokasi pengguna saat ini
        navigator.geolocation.getCurrentPosition(function(position) {
            var userLocation = position.coords.latitude + ',' + position.coords.longitude;
            document.getElementById('user_location').value = userLocation;

            // Menandai lokasi pengguna pada peta dengan marker
            var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);
            marker.bindPopup("Lokasi Anda").openPopup();
        });

        // Submit form setelah verifikasi geofence
        $('#login-form').on('submit', function(event) {
            event.preventDefault();
            
            // Ambil lokasi pengguna dari input tersembunyi
            var userLocation = $('#user_location').val();

            // Kirim AJAX request untuk verifikasi geofence
            $.ajax({
                type: 'POST',
                url: '/verify-geofence',
                data: {
                    location: userLocation
                },
                success: function(response) {
                    if (response.allowed) {
                        // Jika lokasi diizinkan, submit form login
                        $('#login-form')[0].submit();
                    } else {
                        // Jika lokasi tidak diizinkan, tampilkan pesan kesalahan
                        alert('Lokasi Anda tidak diizinkan untuk login. Silakan coba lagi dari lokasi yang sesuai.');
                    }
                },
                error: function() {
                    // Handle error jika terjadi masalah dengan AJAX request
                    alert('Terjadi kesalahan saat verifikasi lokasi. Silakan coba lagi nanti.');
                }
            });
        });
    });
</script>
</body>

</html>
