<footer class="footer">
    <div class="footer-info pt-5">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-4 col-xxl-5">
                    <div class="footer-logo">
                        <img class="pb-5" src="{{ asset('images/logo.png') }}" width="250"  alt="ngaodu">
                        <div class="footer-social">
                            <img src="{{ asset('images/icon/facebook.svg') }}" alt="facebook">
                            <img src="{{ asset('images/icon/instagram.svg') }}" alt="instagram">
                            <img src="{{ asset('images/icon/twitter.svg') }}" alt="twitter">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-xxl-4">
                    <div class="footer-nav row">
                        <div class="col-6 col-sm-5 col-md-4 col-lg-5 p-0">
                            <nav class="nav flex-column">
                                <a class="nav-link" href="{{ route('index') }}">{{ __('client.home') }}</a>
                                <a class="nav-link" href="#">{{ __('client.about') }}</a>
                                <a class="nav-link" href="#">{{ __('client.tours') }}</a>
                                <a class="nav-link" href="#">{{ __('client.hotels') }}</a>
                                <a class="nav-link"
                                   href="{{ route('client.contact.index') }}">{{ __('client.contact') }}</a>
                            </nav>
                        </div>
                        <div class="col-6 col-sm-7 col-md-8 col-lg-7 p-0">
                            <nav class="nav flex-column">
                                <a class="nav-link" href="#">{{ __('client.footer.partner_with_us') }}</a>
                                <a class="nav-link" href="#">{{ __('client.footer.terms_and_conditions') }}</a>
                                <a class="nav-link" href="#">{{ __('client.footer.privacy_policy') }}</a>
                                <a class="nav-link" href="#">{{ __('client.footer.guest_policy') }}</a>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-xl-3">
                    <div class="footer-contact">
                        <div class="contact-item d-flex align-items-start">
                            <div class="icon-contact">
                                <img class="fill-white" src="{{ asset('images/icon/location.svg') }}" alt="address">
                            </div>
                            <p style="margin-left: 5px;">
                                <a href="#">S??? 467 ??. Nguy???n Tr??i,Thanh Xu??n Nam, Thanh Xu??n, H?? N???i</a>
                            </p>
                        </div>
                        <div class="contact-item">
                            <div class="icon-contact">
                                <img class="fill-white" src="{{ asset('images/icon/mail.svg') }}" alt="email">
                            </div>
                            <p><a href="mailto:hello@adamotravel.com">hello@tuyenphamtravel.com</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright">
        <span>Ph???m C??ng Tuy???n 60TH1</span>
    </div>
</footer>
