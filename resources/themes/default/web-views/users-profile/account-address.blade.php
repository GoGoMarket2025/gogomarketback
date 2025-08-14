@extends('layouts.front-end.app')

@section('title', translate('my_Address'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/vendor/nouislider/distribute/nouislider.min.css')}}"/>
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/address.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
@endpush

@section('content')
    <div class="__account-address">
        <div class="modal fade rtl text-align-direction" id="exampleModal" tabindex="-1" role="dialog"
             aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-name">{{translate('add_new_address')}}</h5>
                    </div>
                    <div class="modal-body">
                        <form action="{{route('address-store')}}" method="post">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="donate-now d-flex gap-2 flex-wrap">
                                        <li>
                                            <input type="radio" id="a25" name="addressAs" value="permanent"/>
                                            <label for="a25" class="component">{{translate('permanent')}}</label>
                                        </li>
                                        <li>
                                            <input type="radio" id="a50" name="addressAs" value="home"/>
                                            <label for="a50" class="component">{{translate('home')}}</label>
                                        </li>
                                        <li>
                                            <input type="radio" id="a75" name="addressAs" value="office"
                                                   checked="checked"/>
                                            <label for="a75" class="component">{{translate('office')}}</label>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-6 d-flex">

                                    <ul class="donate-now d-flex gap-2 flex-wrap">
                                        <li>
                                            <input type="radio" name="is_billing" id="b25" value="0" checked/>
                                            <label for="b25" class="billing_component">{{translate('shipping')}}</label>
                                        </li>
                                        <li>
                                            <input type="radio" name="is_billing" id="b50" value="1"/>
                                            <label for="b50" class="billing_component">{{translate('billing')}}</label>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="tab-content">
                                <div id="home" class="container tab-pane active"><br>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="name">
                                                {{translate('contact_person_name')}}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input class="form-control" type="text" id="name" name="name" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="firstName">
                                                {{translate('phone')}}
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input class="form-control phone-input-with-country-picker" id="phone" type="text"
                                                   placeholder="{{ translate('enter_phone_number') }}" required>
                                            <input type="hidden" class="country-picker-phone-number w-50" name="phone" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="address-city">
                                                {{translate('city')}}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input class="form-control" type="text" id="address-city" name="city"
                                                   required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="zip">
                                                {{translate('zip_code')}}
                                            </label>
                                            @if($zip_restrict_status)
                                                <select name="zip" id="" class="form-control selectpicker"
                                                        data-live-search="true">
                                                    @foreach($zip_codes as $code)
                                                        <option
                                                            value="{{ $code->zipcode }}">{{ $code->zipcode }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input class="form-control" type="text" id="zip" name="zip">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label for="address-city">
                                                {{translate('country')}}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select name="country" id="" class="form-control selectpicker"
                                                    data-live-search="true">
                                                @foreach($countries as $d)
                                                    <option value="{{ $d['name'] }}">{{ $d['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-12">
                                            <div class="form-group mb-1">
                                                <label for="address">
                                                    {{translate('address')}}
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <textarea class="form-control" id="address" type="text" name="address"
                                                          required></textarea>

                                                <span class="fs-14 text-danger font-semi-bold opacity-0 map-address-alert">
                                                    {{ translate('note') }}: {{ translate('you_need_to_select_address_from_your_selected_country') }}
                                                </span>
                                            </div>
                                        </div>

                                        @php($default_location=getWebConfig(name: 'default_location'))
                                        @if(getWebConfig('map_api_status') ==1 )
                                            <div class="col-md-12">
                                                <div class="form-group map-area-alert-border location-map-address-canvas-area">
                                                    <input id="pac-input" class="controls rounded __inline-46 location-search-input-field"
                                                           title="{{translate('search_your_location_here')}}" type="text"
                                                           placeholder="{{translate('search_here')}}"/>
                                                    <div class="__h-200px" id="location_map_canvas"></div>
                                                    <button onclick="locateMe()" type="button" class="btn btn--primary mt-3" style="width: 100%">
                                                        📍 Найти меня
                                                    </button>
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                                <input type="hidden" id="latitude"
                                       name="latitude" class="form-control d-inline"
                                       placeholder="{{ translate('ex')}} : -94.22213"
                                       value="{{$default_location?$default_location['lat']:0}}" required readonly>
                                <input type="hidden"
                                       name="longitude" class="form-control"
                                       placeholder="{{ translate('ex')}} : 103.344322" id="longitude"
                                       value="{{$default_location?$default_location['lng']:0}}" required readonly>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">{{translate('close')}}</button>
                                    <button type="submit"
                                            class="btn btn--primary">{{translate('add_information')}}  </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
            <div class="row ">
                @include('web-views.partials._profile-aside')
                <section class="col-lg-9 __customer-profile px-0">

                    <div class="card card-body border-0">
                        <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                            <h5 class="font-bold m-0 fs-16">{{translate('my_Address')}}</h5>
                            <div class="d-flex justify-content-end align-items-center mb-3 gap-2">
                                <button type="submit" class="btn btn--primary text-capitalize btn-sm d-flex align-items-center gap-1" data-toggle="modal"
                                        data-target="#exampleModal" id="add_new_address">
                                    <img src="{{ theme_asset(path: 'public/assets/front-end/img/add-address-icon.png')}}" class=""
                                         alt="">
                                    {{translate('add_address')}}
                                </button>

                                <div class="d-flex justify-content-end d-lg-none">
                                    <button class="profile-aside-btn btn btn--primary px-2 rounded px-2 py-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15"
                                             fill="none">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                  d="M7 9.81219C7 9.41419 6.842 9.03269 6.5605 8.75169C6.2795 8.47019 5.898 8.31219 5.5 8.31219C4.507 8.31219 2.993 8.31219 2 8.31219C1.602 8.31219 1.2205 8.47019 0.939499 8.75169C0.657999 9.03269 0.5 9.41419 0.5 9.81219V13.3122C0.5 13.7102 0.657999 14.0917 0.939499 14.3727C1.2205 14.6542 1.602 14.8122 2 14.8122H5.5C5.898 14.8122 6.2795 14.6542 6.5605 14.3727C6.842 14.0917 7 13.7102 7 13.3122V9.81219ZM14.5 9.81219C14.5 9.41419 14.342 9.03269 14.0605 8.75169C13.7795 8.47019 13.398 8.31219 13 8.31219C12.007 8.31219 10.493 8.31219 9.5 8.31219C9.102 8.31219 8.7205 8.47019 8.4395 8.75169C8.158 9.03269 8 9.41419 8 9.81219V13.3122C8 13.7102 8.158 14.0917 8.4395 14.3727C8.7205 14.6542 9.102 14.8122 9.5 14.8122H13C13.398 14.8122 13.7795 14.6542 14.0605 14.3727C14.342 14.0917 14.5 13.7102 14.5 13.3122V9.81219ZM12.3105 7.20869Л14.3965 5.12269C14.982 4.53719 14.982 3.58719 14.3965 3.00169L12.3105 0.915687C11.725 0.330188 10.775 0.330188 10.1895 0.915687L8.1035 3.00169C7.518 3.58719 7.518 4.53719 8.1035 5.12269L10.1895 7.20869C10.775 7.79419 11.725 7.79419 12.3105 7.20869ZM7 2.31219C7 1.91419 6.842 1.53269 6.5605 1.25169C6.2795 0.970186 5.898 0.812187 5.5 0.812187C4.507 0.812187 2.993 0.812187 2 0.812187C1.602 0.812187 1.2205 0.970186 0.939499 1.25169C0.657999 1.53269 0.5 1.91419 0.5 2.31219V5.81219C0.5 6.21019 0.657999 6.59169 0.939499 6.87269C1.2205 7.15419 1.602 7.31219 2 7.31219H5.5C5.898 7.31219 6.2795 7.15419 6.5605 6.87269C6.842 6.59169 7 6.21019 7 5.81219V2.31219Z"
                                                  fill="white"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                        </div>
                        @if ($shippingAddresses->count() ==0)
                            <div class="text-center text-capitalize pb-5 pt-5">
                                <img class="mb-4" src="{{theme_asset(path: 'public/assets/front-end/img/icons/address.svg')}}"
                                     alt="" width="70">
                                <h5 class="fs-14">{{translate('no_address_found')}}!</h5>
                            </div>
                        @endif
                        <div class="row g-3">

                            @foreach($shippingAddresses as $shippingAddress)
                                <section class="col-lg-6 col-md-6">
                                    <div class="card __shadow h-100">
                                        <div
                                            class="card-header d-flex justify-content-between d-flex align-items-center bg-aliceblue border-0">
                                            <div class="w-0 flex-grow-1">
                                                <h6 class="mb-0 fw-semibold">
                                                    {{translate($shippingAddress['address_type'])}}
                                                    <span class="font-semibold fs-12">( {{$shippingAddress['is_billing']==1?translate('Billing_address'):translate('shipping_address')}} )</span>
                                                </h6>
                                            </div>
                                            <div class="d-flex justify-content-between gap-2 align-items-center">
                                                <a class="" title="Edit Address" id="edit"
                                                   href="{{route('address-edit',$shippingAddress->id)}}">
                                                    <img
                                                        src="{{theme_asset(path: 'public/assets/front-end/img/address-edit-icon.png')}}"
                                                        width="19" alt="">
                                                </a>

                                                <button class="no-gutter remove-address-by-modal"
                                                        data-link="{{ route('address-delete',['id'=>$shippingAddress->id])}}">
                                                    <i class="fa fa-trash fa-lg"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <div>
                                                <span class="font-nameA">
                                                    <span class="fw-semibold min-w-60px">
                                                        {{translate('Name')}} </span>
                                                    <span class="px-1">:</span>
                                                    {{$shippingAddress['contact_person_name']}}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-nameA">
                                                    <span class="fw-semibold min-w-60px">
                                                        {{translate('phone')}}
                                                    </span>
                                                    <span class="px-1">:</span>
                                                    {{$shippingAddress['phone']}}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-nameA">
                                                    <span class="fw-semibold min-w-60px">
                                                        {{translate('city')}}
                                                    </span>
                                                    <span class="px-1">:</span>
                                                    {{$shippingAddress['city']}}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-nameA">
                                                    <span class="fw-semibold min-w-60px">
                                                        {{translate('zip_code')}}
                                                    </span>
                                                    <span class="px-1">:</span>
                                                    {{$shippingAddress['zip']}}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-nameA">
                                                    <span class="fw-semibold min-w-60px">
                                                        {{translate('address')}}
                                                    </span>
                                                    <span class="px-1">:</span>
                                                    {{$shippingAddress['address']}}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-nameA">
                                                    <span class="fw-semibold min-w-60px">
                                                        {{translate('country')}}
                                                    </span>
                                                    <span class="px-1">:</span>
                                                    {{$shippingAddress['country']}}
                                                </span>
                                            </div>

                                        </div>
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <span id="system-country-restrict-status" data-value="{{ $country_restrict_status }}"></span>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/account-address.js') }}"></script>
    <script>
        "use strict";
        const deliveryRestrictedCountries = @json($countriesCode);
        function deliveryRestrictedCountriesCheck(countryOrCode, elementSelector, inputElement) {
            const foundIndex = deliveryRestrictedCountries.findIndex(countryCode => countryCode.toLowerCase() === countryOrCode.toLowerCase());
            if (foundIndex !== -1) {
                $(elementSelector).removeClass('map-area-alert-danger');
                $(inputElement).parent().find('.map-address-alert').removeClass('opacity-100').addClass('opacity-0')
            } else {
                $(elementSelector).addClass('map-area-alert-danger');
                $(inputElement).val('')
                $(inputElement).parent().find('.map-address-alert').removeClass('opacity-0').addClass('opacity-100')
            }
        }
    </script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>

    @if(getWebConfig('map_api_status') ==1 )
        {{-- Яндекс.Карты вместо Google Maps --}}
        <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=ee4968dc-1434-49e5-8a95-a63813385c14" defer></script>
        <script>
            "use strict";

            // Глобальные переменные карты и маркера
            let ymap, yPlacemark, ySuggest;

            // Хэлпер: запись координат в скрытые поля
            function updateCoordinates(lat, lng) {
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            }

            // Хэлпер: достаем страну/код страны из geoObject
            function extractCountryInfo(geoObject) {
                // addressComponents содержит массив {kind, name}
                const comps = geoObject.getAddressComponents ? geoObject.getAddressComponents() : [];
                const country = comps.find(c => c.kind === 'country')?.name || '';
                // country_code можно взять из metaDataProperty
                const meta = geoObject.getMetaDataProperty && geoObject.getMetaDataProperty('metaDataProperty.GeocoderMetaData');
                const code = meta && meta.Address && meta.Address.country_code ? meta.Address.country_code : '';
                return { country, code };
            }

            // Хэлпер: достаем части адреса и собираем похожую строку как раньше
            function buildAddressFromGeoObject(geoObject) {
                const comps = geoObject.getAddressComponents ? geoObject.getAddressComponents() : [];

                const get = (kind) => comps.find(c => c.kind === kind)?.name || '';

                const region = get('province') || get('area');
                const district = get('district');
                const street = get('street');
                const streetNumber = get('house');

                const meta = geoObject.getMetaDataProperty('metaDataProperty.GeocoderMetaData');
                const country = (meta && meta.Address && meta.Address.country_name) || '';
                const countryCode = (meta && meta.Address && meta.Address.country_code) || '';

                const parts = [
                    region,
                    district,
                    [streetNumber, street].filter(Boolean).join(' '),
                    country || countryCode
                ].filter(Boolean);

                return parts.join(', ');
            }

            // Основной реверс-геокодинг и проверки
            function resolveAndFillAddress(coords) {
                ymaps.geocode(coords).then(function (res) {
                    const first = res.geoObjects.get(0);
                    if (!first) return;

                    // Адресная строка (собираем вручную, чтобы соответствовало прежней логике)
                    const addr = buildAddressFromGeoObject(first);
                    document.getElementById('address').value = addr;

                    // Проверка страны
                    const systemCountryRestrictStatus = $('#system-country-restrict-status').data('value');
                    if (systemCountryRestrictStatus) {
                        const { code } = extractCountryInfo(first);
                        if (code) {
                            deliveryRestrictedCountriesCheck(code, '.location-map-address-canvas-area', '#address');
                        }
                    }
                });
            }

            // Инициализация карты
            function initYMap() {
                const lat = {{$default_location ? $default_location['lat'] : '55.751244'}};
                const lng = {{$default_location ? $default_location['lng'] : '37.618423'}};

                ymap = new ymaps.Map('location_map_canvas', {
                    center: [lat, lng],
                    zoom: 13,
                    controls: ['zoomControl']
                });

                yPlacemark = new ymaps.Placemark([lat, lng], {}, { draggable: true });
                ymap.geoObjects.add(yPlacemark);

                // Перетаскивание маркера
                yPlacemark.events.add('dragend', function () {
                    const coords = yPlacemark.geometry.getCoordinates();
                    updateCoordinates(coords[0], coords[1]);
                    resolveAndFillAddress(coords);
                });

                // Клик по карте
                ymap.events.add('click', function (e) {
                    const coords = e.get('coords');
                    yPlacemark.geometry.setCoordinates(coords);
                    updateCoordinates(coords[0], coords[1]);
                    resolveAndFillAddress(coords);
                });

                // Suggest на наш инпут #pac-input
                const input = document.getElementById('pac-input');
                if (input) {
                    ySuggest = new ymaps.SuggestView('pac-input');
                    // По Enter сразу геокодим выбранный текст
                    input.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const query = input.value.trim();
                            if (!query) return;
                            ymaps.geocode(query).then(function (res) {
                                const first = res.geoObjects.get(0);
                                if (!first) return;
                                const coords = first.geometry.getCoordinates();
                                ymap.setBounds(res.geoObjects.getBounds(), { checkZoomRange: true }).then(() => {
                                    ymap.setCenter(coords);
                                    yPlacemark.geometry.setCoordinates(coords);
                                    updateCoordinates(coords[0], coords[1]);
                                    resolveAndFillAddress(coords);
                                });
                            });
                        }
                    });
                    // При клике по подсказке
                    ySuggest.events.add('select', function (e) {
                        const value = e.get('item').value;
                        ymaps.geocode(value).then(function (res) {
                            const first = res.geoObjects.get(0);
                            if (!first) return;
                            const coords = first.geometry.getCoordinates();
                            ymap.setBounds(res.geoObjects.getBounds(), { checkZoomRange: true }).then(() => {
                                ymap.setCenter(coords);
                                yPlacemark.geometry.setCoordinates(coords);
                                updateCoordinates(coords[0], coords[1]);
                                resolveAndFillAddress(coords);
                            });
                        });
                    });
                }

                // Начальные координаты в инпуты
                updateCoordinates(lat, lng);
            }

            // 📍 «Найти меня» — аналог твоей функции
            function locateMeImpl() {
                if (!navigator.geolocation) {
                    alert("Ваш браузер не поддерживает геолокацию.");
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const coords = [pos.coords.latitude, pos.coords.longitude];
                        if (!ymap || !yPlacemark) {
                            alert("Карта или маркер не инициализированы.");
                            return;
                        }
                        yPlacemark.geometry.setCoordinates(coords);
                        ymap.setCenter(coords, 15);
                        updateCoordinates(coords[0], coords[1]);
                        resolveAndFillAddress(coords);
                    },
                    (err) => {
                        alert("Ошибка геолокации: " + err.message);
                    }
                );
            }

            // Делаем функцию доступной глобально, как у тебя
            window.locateMe = locateMeImpl;

            // Инициализация после готовности API
            (function waitYmapsReady() {
                if (window.ymaps && ymaps.ready) {
                    ymaps.ready(initYMap);
                } else {
                    setTimeout(waitYmapsReady, 50);
                }
            })();

            // Блокируем Enter в input-ах формы (как у тебя)
            $(document).on("keydown", "input", function (e) {
                if (e.which === 13) e.preventDefault();
            });
        </script>
    @endif
@endpush
