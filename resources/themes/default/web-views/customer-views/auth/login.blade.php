@extends('layouts.front-end.app')

@section('title', translate('sign_in'))

@section('content')

<div class="text-align-direction">
    <div class="login-card">
        <div class="mx-auto __max-w-360 color-bc">
            <h2 class="text-center h4 mb-4 font-bold text-capitalize fs-18-mobile">Log In or Sign Up</h2>
      <hr>
            <div class="number-verification mt-3">
                <!-- <p class="text-center">Enter your mobile number</p>
                    <h5 class="text-center">We Will Send you On OTP Message </h5> -->
                <div>
                    <form class="needs-validation mt-2" autocomplete="off" 
      action="{{route('customer.auth.login')}}"
      method="post" id="customer-login-form">
    @csrf

    <div class="form-group">
        <input class="form-control text-align-direction" 
               type="text" 
               name="mobile_number" 
               id="mobile_number"
               value="{{old('mobile_number')}}" 
               placeholder="Enter Mobile Number"
               maxlength="10"
               required>

        <span class="text-danger" id="mobile-error"></span>
    </div>

    <div class="form-group show-otp" style="display: none">
        <input class="form-control text-align-direction" 
               type="text" 
               name="otp" 
               id="otp-number" 
               placeholder="Enter OTP"
               maxlength="6">
        <span class="text-danger" id="otp-error"></span>
    </div>

    <span class="verifyTimer"></span>

    <button class="btn btn--primary btn-block btn-shadow getotp hide-button" type="submit">
        GET OTP
    </button>

    <button style="display: none" 
            class="btn btn--primary btn-block btn-shadow VARIFYOTP show-button" 
            type="submit">
        VERIFY OTP
    </button>
</form>
                    @if($web_config['social_login_text'])
                    <div class="text-center m-3 text-black-50">
                        <small>{{ translate('or_continue_with') }}</small>
                    </div>
                    @endif
                    <div class=" my-3 gap-2">
                        @foreach (getWebConfig(name: 'social_login') as $socialLoginService)
                        @if (isset($socialLoginService) && $socialLoginService['status'])
                        <div class=" block-verified bg-white">
                            <a class="d-block mt-2 text-center bl-block" href="{{ route('customer.auth.service-login', $socialLoginService['login_medium']) }}">
                                <img src="{{theme_asset(path: 'public/assets/front-end/img/icons/'.$socialLoginService['login_medium'].'.png') }}" alt="">
                                Google With Continue
                            </a>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    {{-- <div class="text-black-50 text-center">
                        <small>
                            {{ translate('Enjoy_New_experience') }}
                            <a class="text-primary text-underline" href="{{route('customer.auth.sign-up')}}">
                                {{ translate('sign_up') }}
                            </a>
                        </small>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script')


<script>
document.addEventListener("DOMContentLoaded", function () {

    const mobileInput = document.getElementById("mobile_number");
    const mobileError = document.getElementById("mobile-error");
    const form = document.getElementById("customer-login-form");

    // 🔹 Only numbers allow
    mobileInput.addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9]/g, '');

        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }

        validateMobile();
    });

    // 🔹 Validation function
    function validateMobile() {
        let mobile = mobileInput.value;

        if (mobile.length === 0) {
            mobileError.innerText = "Mobile number is required";
            return false;
        }

        if (mobile.length !== 10) {
            mobileError.innerText = "Mobile number must be exactly 10 digits";
            return false;
        }

        // Indian mobile validation (starts with 6-9)
        let regex = /^[6-9]\d{9}$/;

        if (!regex.test(mobile)) {
            mobileError.innerText = "Enter valid mobile number";
            return false;
        }

        mobileError.innerText = "";
        return true;
    }

    // 🔹 Form submit validation
    form.addEventListener("submit", function (e) {
        if (!validateMobile()) {
            e.preventDefault(); // stop form submit
        }
    });

});
</script>
  <script>
let timerRunning = false; // Flag to check if the timer is already running
$(".getotp").on("click", function (e) {
    e.preventDefault();
    let mobile = $("#mobile_number").val();
    $("#loading").css("display", "block");
    // Make the AJAX request
    $.ajax({
        url: "{{url('api/v1/auth/send-otp')}}",
        type: "POST",
        headers: {
            "Authorization": "Bearer " + "sfdsd12345678",
        },
        contentType: "application/json",
        data: JSON.stringify({
            phone: mobile,
            _token: $('meta[name="csrf-token"]').attr("content"),
        }),
        success: function (data) {
            console.log("FULL RESPONSE:", data);
            if (data.status == true) {
                $("#loading").css("display", "none");
                if (!timerRunning) {
                    timerRunning = true;
                    let countDownDate = new Date().getTime() + 58000;
                    let x = setInterval(function () {
                        let now = new Date().getTime();
                        let distance = countDownDate - now;
                        let seconds = Math.max(Math.floor((distance % (1000 * 60)) / 1000), 0);

                        $(".verifyTimer").html("Resend Code (in " + seconds + " Secs)");

                        if (distance < 0) {
                            clearInterval(x);
                            timerRunning = false;
                            $(".resend-otp-button").prop("disabled", false);
                            $(".show-button").hide();
                            $(".hide-button").show();
                            $(".show-otp").hide();
                        }
                    }, 1000);
                }

                $(".resend-otp-button").prop("disabled", true);
                setTimeout(function () {
                    $(".resend-otp-button").prop("disabled", false);
                }, 10000);

                $(".add-fadein").html("");
                $(".hide-button").hide();
                $(".show-button").show();
                $(".show-otp").show();
                console.log("Otp sent");
            } else {
                $(".add-fadein").html(data.errors[0].message);
            }
        },
    });
});


    $(".VARIFYOTP").on("click", function (e) {
        e.preventDefault();
        let mobile = $("#mobile_number").val();
        let otp = $("#otp-number").val();
        $("#loading").css("display", "block");
        $.ajax({
            url: "{{url('customer/auth/otp-verify-web')}}",
            type: "POST",
            headers: {
                "Authorization": "Bearer " + "sfdsd12345678",
            },
            contentType: "application/json",
            data: JSON.stringify({
                phone: mobile,
                otp: otp,
                cm_firebase_token:"2342354435435",
                _token: "{{ csrf_token() }}",
            }),
            success: function (data) {
                if (data.status == true) {
                    $("#loading").css("display", "none");
                    console.log(data.profile_status);
                    if(data.profile_status==true){
                        window.location.href = "{{url('/')}}";
                    } else {
                        window.location.href = "{{url('user-account')}}";
                    }
                } else {
                    if(typeof data.errors !== 'undefined' && data.errors[0] !== ''){
                        $(".add-fadein").html(data.errors[0].message);
                    }else{
                        $(".add-fadein-otp").html("Verification Failed. Please try again.");
                    }
                }
            }
        });
    });
</script>
@if(isset($recaptcha) && $recaptcha['status'] == 1)
<script type="text/javascript">
    "use strict";
    var onloadCallback = function() {
        grecaptcha.render('recaptcha_element', {
            'sitekey': '{{ getWebConfig(name: '
            recaptcha ')['
            site_key '] }}'
        });
    };
</script>
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
    async defer></script>
@endif
@endpush
