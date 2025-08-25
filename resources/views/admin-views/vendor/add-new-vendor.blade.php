@extends('layouts.admin.app')

@section('title', translate('add_new_Vendor'))

@section('content')
    <div class="content container-fluid main-card {{Session::get('direction') }}">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-non-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" class="mb-1"
                     alt="">
                {{ translate('add_new_Vendor') }}
            </h2>
        </div>

        <form action="{{ route('admin.vendors.add') }}" method="post" enctype="multipart/form-data"
              id="add-vendor-form" data-message="{{ translate('want_to_add_this_vendor').'?'}}"
              data-redirect-route="{{ route('admin.vendors.vendor-list') }}">
            @csrf
            <div class="card">
                <div class="card-body">
                    <input type="hidden" name="status" value="approved">
                    <h3 class="mb-0 text-non-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/vendor-information.png') }}"
                             class="mb-1" alt="">
                        {{ translate('vendor_information') }}
                    </h3>
                    <div class="row align-items-center">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <div class="form-group">
                                <label for="exampleFirstName"
                                       class="mb-2 d-flex gap-1 align-items-center">{{ translate('first_name') }}</label>
                                <input type="text" class="form-control form-control-user" id="exampleFirstName"
                                       name="f_name" value="{{ old('f_name') }}"
                                       placeholder="{{ translate('ex') }}: Jhone"
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="exampleLastName"
                                       class="mb-2 d-flex gap-1 align-items-center">{{ translate('last_name') }}</label>
                                <input type="text" class="form-control form-control-user" id="exampleLastName"
                                       name="l_name" value="{{ old('l_name') }}"
                                       placeholder="{{ translate('ex') }}: Doe"
                                       required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="phone_number"
                                       class=" mb-2">{{translate('phone_number')}}</label>
                                <input class="form-control form-control-user"
                                       type="tel" value=""
                                       placeholder="{{ translate('ex').': 017xxxxxxxx' }}" name="phone" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="d-flex justify-content-center">
                                    <img class="upload-img-view" id="viewer"
                                         src="{{ dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}"
                                         alt="{{ translate('banner_image') }}"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="mb-2 d-flex gap-1 align-items-center">{{ translate('vendor_Image') }} <span
                                        class="text-info">({{ translate('ratio') }} {{ translate('1') }}:{{ translate('1') }})</span>
                                </div>
                                <div class="custom-file text-left">
                                    <input type="file" name="image" id="custom-file-upload"
                                           class="custom-file-input image-input"
                                           data-image-id="viewer"
                                           accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label"
                                           for="custom-file-upload">{{ translate('upload_image') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <input type="hidden" name="status" value="approved">
                    <h3 class="mb-0 text-non-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/vendor-information.png') }}"
                             class="mb-1" alt="">
                        {{ translate('account_information') }}
                    </h3>
                    <div class="row">
                        <div class="col-lg-4 form-group">
                            <label for="exampleInputEmail"
                                   class="mb-2 d-flex gap-1 align-items-center">{{ translate('email') }}</label>
                            <input type="email" class="form-control form-control-user" id="exampleInputEmail"
                                   name="email" value="{{ old('email') }}"
                                   placeholder="{{ translate('ex').':'.'Jhone@company.com'}}" required>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label for="user_password" class="mb-2 d-flex gap-1 align-items-center">
                                {{ translate('password') }}
                                <span class="input-label-secondary cursor-pointer d-flex" data-bs-toggle="tooltip"
                                      data-bs-title="{{ translate('The_password_must_be_at_least_8_characters_long_and_contain_at_least_one_uppercase_letter').','.translate('_one_lowercase_letter').','.translate('_one_digit_').','.translate('_one_special_character').','.translate('_and_no_spaces').'.'}}">
                                <i class="fi fi-rr-info"></i>
                            </span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="js-toggle-password form-control password-check"
                                       name="password" required id="user_password" minlength="8"
                                       placeholder="{{ translate('password_minimum_8_characters') }}">
                                <div id="changePassTarget" class="input-group-append changePassTarget">
                                    <a class="text-body-light" href="javascript:">
                                        <i id="changePassIcon" class="fi fi-rr-eye"></i>
                                    </a>
                                </div>
                            </div>
                            <span class="text-danger mx-1 password-error"></span>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label for="confirm_password"
                                   class="mb-2 d-flex gap-1 align-items-center">{{ translate('confirm_password') }}</label>
                            <div class="input-group">
                                <input type="password" class="js-toggle-password form-control" name="confirm_password"
                                       required id="confirm_password" placeholder="{{ translate('confirm_password') }}">
                                <div id="changeConfirmPassTarget" class="input-group-append changePassTarget">
                                    <a class="text-body-light" href="javascript:">
                                        <i id="changeConfirmPassIcon" class="fi fi-rr-eye"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="pass invalid-feedback">{{ translate('repeat_password_not_match').'.'}}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h3 class="mb-0 text-non-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/vendor-information.png') }}"
                             class="mb-1" alt="">
                        {{ translate('shop_information') }}
                    </h3>

                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label for="shop_name"
                                   class="mb-2 d-flex gap-1 align-items-center">{{ translate('shop_name') }}</label>
                            <input type="text" class="form-control form-control-user" id="shop_name" name="shop_name"
                                   placeholder="{{ translate('ex').':'.translate('Jhon') }}"
                                   value="{{ old('shop_name') }}"
                                   required>
                        </div>
                        <div class="col-lg-6 form-group">
                            <label for="shop_address"
                                   class="mb-2 d-flex gap-1 align-items-center">{{ translate('shop_address') }}</label>
                            <textarea name="shop_address" class="form-control text-area-max" id="shop_address" rows="1"
                                      placeholder="{{ translate('ex').':'.translate('doe') }}">{{ old('shop_address') }}</textarea>
                        </div>

                        <div class="col-lg-6 form-group">
                            <label for="latitude"
                                   class="mb-2 d-flex gap-1 align-items-center">Широта</label>
                            <input type="text" class="form-control form-control-user" id="latitude" name="latitude"
                                   placeholder="{{ translate('ex').':'.translate('широта -90..90') }}"
                                   value="{{ old('latitude') }}"
                                   required>
                        </div>

                        <div class="col-lg-6 form-group">
                            <label for="longitude"
                                   class="mb-2 d-flex gap-1 align-items-center">Долгота</label>
                            <input type="text" class="form-control form-control-user" id="longitude" name="longitude"
                                   placeholder="{{ translate('ex').':'.translate('долгота -180..180') }}"
                                   value="{{ old('longitude') }}"
                                   required>
                        </div>

                        {{-- Карта для выбора координат --}}
                        @php($default_location = getWebConfig(name: 'default_location'))
                        @if(getWebConfig('map_api_status') == 1)
                        <div class="form-group">
                            <div class="map-area-alert-border">
                            <input id="pac-input-merchant"
                                    class="controls rounded __inline-46 location-search-input-field"
                                    type="text"
                                    placeholder="{{translate('search_here')}}"
                                    title="{{translate('search_your_location_here')}}" />
                            <div id="location_map_canvas_merchant" style="height: 220px; border-radius: 8px;"></div>
                            <button type="button" class="btn btn--primary mt-3 w-100" onclick="locateMeMerchant()">
                                📍 {{ translate('locate_me') }}
                            </button>
                            </div>
                        </div>
                        @endif

                        <div class="col-lg-6 form-group">
                            <label for="vat_percent"
                                   class="mb-2 d-flex gap-1 align-items-center">Ставка НДС</label>
                            <input type="text" class="form-control form-control-user" id="vat_percent"
                                   name="vat_percent"
                                   placeholder="{{ translate('ex').': 0 или 12' }}" value="{{ old('vat_percent') }}"
                                   required>
                        </div>

                        <div class="col-lg-6 form-group">
                            <label for="identification_number"
                                   class="mb-2 d-flex gap-1 align-items-center">ИНН / ПИНФЛ</label>
                            <input type="text" class="form-control form-control-user" id="identification_number"
                                   name="identification_number"
                                   placeholder="{{ translate('ex').': ИНН или ПИНФЛ' }}"
                                   value="{{ old('identification_number') }}"
                                   required>
                        </div>

                        <div class="col-lg-6 form-group">
                            <div class="d-flex justify-content-center">
                                <img class="upload-img-view" id="viewerLogo"
                                     src="{{ dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}"
                                     alt="{{ translate('banner_image') }}"/>
                            </div>

                            <div class="mt-4">
                                <div class="d-flex gap-1 align-items-center mb-2">
                                    {{ translate('shop_logo') }}
                                    <span class="text-info">({{ translate('ratio').' '.'1:1'}})</span>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="logo" id="logo-upload"
                                           class="custom-file-input image-input"
                                           data-image-id="viewerLogo"
                                           accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label"
                                           for="logo-upload">{{ translate('upload_logo') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 form-group">
                            <div class="d-flex justify-content-center">
                                <img class="upload-img-view upload-img-view__banner" id="viewerBanner"
                                     src="{{ dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}"
                                     alt="{{ translate('banner_image') }}"/>
                            </div>
                            <div class="mt-4">
                                <div class="d-flex gap-1 align-items-center mb-2">
                                    {{ translate('shop_banner') }}
                                    <span
                                        class="text-info">{{ THEME_RATIO[theme_root_path()]['Store cover Image'] }}</span>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="banner" id="banner-upload"
                                           class="custom-file-input image-input"
                                           data-image-id="viewerBanner"
                                           accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label text-non-capitalize"
                                           for="banner-upload">{{ translate('upload_Banner') }}</label>
                                </div>
                            </div>
                        </div>

                        @if(theme_root_path() == "theme_aster")
                            <div class="col-lg-6 form-group">
                                <div class="d-flex justify-content-center">
                                    <img class="upload-img-view upload-img-view__banner" id="viewerBottomBanner"
                                         src="{{ dynamicAsset(path: 'public/assets/back-end/img/400x400/img2.jpg') }}"
                                         alt="{{ translate('banner_image') }}"/>
                                </div>

                                <div class="mt-4">
                                    <div class="d-flex gap-1 align-items-center title-color mb-2">
                                        {{ translate('shop_secondary_banner') }}
                                        <span
                                            class="text-info">{{ THEME_RATIO[theme_root_path()]['Store Banner Image'] }}</span>
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" name="bottom_banner" id="bottom-banner-upload"
                                               class="custom-file-input image-input"
                                               data-image-id="viewerBottomBanner"
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label text-non-capitalize"
                                               for="bottom-banner-upload">{{ translate('upload_bottom_banner') }}</label>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-10">
                        <input type="hidden" name="from_submit" value="admin">
                        <button type="reset" class="btn btn-secondary reset-button">{{ translate('reset') }} </button>
                        <button type="submit" class="btn btn-primary btn-user">{{ translate('submit') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/admin/vendor.js') }}"></script>
    @if(getWebConfig('map_api_status') == 1)
  <script
    src="https://maps.googleapis.com/maps/api/js?key={{ getWebConfig('map_api_key') }}&callback=callBackMerchantMap&loading=async&libraries=places&v=3.56"
    defer>
  </script>
  <script>
    "use strict";

    async function initMerchantMap() {
      // стартовая точка
      const startLat = {{ $default_location ? $default_location['lat'] : '41.3111' }};   // Tashkent by default
      const startLng = {{ $default_location ? $default_location['lng'] : '69.2797' }};

      const latInput = document.getElementById('latitude');
      const lngInput = document.getElementById('longitude');
      const addrArea  = document.getElementById('shop_address'); // опционально, если есть поле адреса

      // если уже есть значения в инпутах — используем их как старт
      const center = {
        lat: parseFloat(latInput?.value || startLat) || startLat,
        lng: parseFloat(lngInput?.value || startLng) || startLng,
      };

      const { Map } = await google.maps.importLibrary("maps");
      const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
      const mapEl = document.getElementById('location_map_canvas_merchant');

      const map = new Map(mapEl, {
        center,
        zoom: 13,
        mapId: 'roadmap'
      });

      const marker = new AdvancedMarkerElement({
        map,
        position: center
      });

      // в глобальную область — пригодится в locateMeMerchant()
      window.__merchantMap = map;
      window.__merchantMarker = marker;

      const geocoder = new google.maps.Geocoder();

      // клик по карте — ставим маркер и пишем координаты
      map.addListener('click', (e) => {
        const coords = e.latLng.toJSON();
        marker.position = coords;
        map.panTo(e.latLng);

        latInput.value = coords.lat;
        lngInput.value = coords.lng;

        // обратное геокодирование (опционально — в #shop_address)
        if (addrArea) {
          geocoder.geocode({ location: coords }, (results, status) => {
            if (status === "OK" && results?.[0]) {
              addrArea.value = buildAddressString(results[0].address_components);
            }
          });
        }
      });

      // поиск по places
      const searchInput = document.getElementById('pac-input-merchant');
      const searchBox = new google.maps.places.SearchBox(searchInput);
      map.controls[google.maps.ControlPosition.TOP_CENTER].push(searchInput);

      map.addListener("bounds_changed", () => {
        searchBox.setBounds(map.getBounds());
      });

      let searchMarkers = [];
      searchBox.addListener("places_changed", () => {
        const places = searchBox.getPlaces();
        if (!places || !places.length) return;

        // убрать старые маркеры
        searchMarkers.forEach(m => m.setMap && m.setMap(null));
        searchMarkers = [];

        const bounds = new google.maps.LatLngBounds();

        places.forEach((place) => {
          if (!place.geometry || !place.geometry.location) return;

          const m = new AdvancedMarkerElement({
            map,
            position: place.geometry.location,
            title: place.name
          });

          // клик по маркеру из поиска — зафиксировать координаты в инпуты
          m.addListener?.('gmp-click', () => {
            const p = m.position; // google.maps.LatLng|object
            const lat = typeof p.lat === 'function' ? p.lat() : p.lat;
            const lng = typeof p.lng === 'function' ? p.lng() : p.lng;
            latInput.value = lat;
            lngInput.value = lng;
            marker.position = { lat, lng };
          });

          searchMarkers.push(m);

          if (place.geometry.viewport) bounds.union(place.geometry.viewport);
          else bounds.extend(place.geometry.location);
        });

        map.fitBounds(bounds);
      });

      // вспомогалки
      function get(comp, type) {
        return comp.find(c => c.types.includes(type))?.long_name || '';
      }
      function buildAddressStringFromComponents(components){
        const region       = get(components, 'administrative_area_level_1');
        const district     = get(components, 'administrative_area_level_2') || get(components, 'locality');
        const street       = get(components, 'route');
        const streetNumber = get(components, 'street_number');
        const parts = [
          region,
          district,
          [streetNumber, street].filter(Boolean).join(' ')
        ];
        return parts.filter(Boolean).join(', ');
      }
      window.buildAddressString = buildAddressStringFromComponents; // экспорт для клик/геокодера
    }

    // кнопка «Найти меня»
    function locateMeMerchant() {
      if (!navigator.geolocation) {
        alert("{{ translate('your_browser_does_not_support_geolocation') }}");
        return;
      }
      const map = window.__merchantMap;
      const marker = window.__merchantMarker;
      const latInput = document.getElementById('latitude');
      const lngInput = document.getElementById('longitude');
      const addrArea  = document.getElementById('shop_address');

      navigator.geolocation.getCurrentPosition((pos) => {
        const coords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        marker.position = coords;
        map.setCenter(coords);
        map.setZoom(15);

        latInput.value = coords.lat;
        lngInput.value = coords.lng;

        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: coords }, (results, status) => {
          if (status === "OK" && results?.[0] && addrArea) {
            addrArea.value = buildAddressString(results[0].address_components);
          }
        });
      }, (err) => {
        alert("{{ translate('geolocation_error') }}: " + err.message);
      });
    }

    // колбэк из script src
    function callBackMerchantMap(){
      initMerchantMap();
    }
  </script>
@endif
@endpush
