@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('2FA Verification') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('proses2fa') }}">
                            @csrf

                            <div class="form-group row">
                                <label for="code" class="col-md-4 col-form-label text-md-right">{{ __('2FA Code') }}</label>

                                <div class="col-md-6">
                                    <input id="code" type="text" class="form-control @error('code') is-invalid @enderror" name="code" required autocomplete="code">
                                    @error('code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Verify 2FA Code') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if(isset($qrCodeImage))
                            <img src="data:image/png;base64,{{ $qrCodeImage }}" alt="QR Code">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection