<div class="second-el d--none">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-4">{{translate('create_an_account')}}</h3>
                        <div class="border p-3 p-xl-4 rounded">
                            <h4 class="mb-3">Информация о руководители</h4>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group mb-4">
                                        <label  for="f_name">{{translate('first_name')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="f_name" placeholder="{{translate('ex').': John'}}" required>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label  for="l_name">{{translate('last_name')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="l_name" placeholder="{{translate('ex').': Doe'}}" required>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label  for="passport_serial">{{translate('passport_serial')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="passport_serial" placeholder="AA" required>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label  for="passport_number">{{translate('passport_number')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="passport_number" placeholder="123456" required>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label  for="passport_issue_name">{{translate('passport_issue_name')}} <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="passport_issue_name" placeholder="Кем выдан" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex flex-column gap-3 align-items-center">
                                        <div class="upload-file">
                                            <input type="file" class="upload-file__input" name="image" accept="image/*" required>
                                            <div class="upload-file__img">
                                                <div class="temp-img-box">
                                                    <div class="d-flex align-items-center flex-column gap-2">
                                                        <i class="tio-upload fs-30"></i>
                                                        <div class="fs-12 text-muted text-non-capitalize">{{translate('upload_file')}}</div>
                                                    </div>
                                                </div>
                                                <img src="#" class="dark-support img-fit-contain border" alt="" hidden>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                            <h6 class="text-uppercase mb-1 fs-14">{{translate('vendor_image')}}</h6>
                                            <div class="text-muted text-non-capitalize fs-12">{{translate('image_ratio').' '.'1:1'}}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border p-3 p-xl-4 rounded mt-4">
                            <h4 class="mb-3 text-non-capitalize">{{translate('shop_information')}}</h4>

                            <div class="form-group mb-4">
                                <label for="store_name" class="text-non-capitalize">{{translate('shop_Name')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="shop_name"  name="shop_name" placeholder="{{translate('Ex: XYZ store')}}" required>
                            </div>
                            <div class="form-group mb-4">
                                <label for="store_address" class="text-non-capitalize">{{translate('shop_address')}} <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="shop_address" id="shop_address" rows="4" placeholder="{{translate('shop_address')}}" required></textarea>
                            </div>

                            <!-- ORGANIZATION TYPE (styled like payment radios) -->
                            <div class="form-group mb-4" id="orgTypeGroup">
                                <label for="org_type_ip" class="text-non-capitalize">
                                    {{ translate('organization_type') }} <span class="text-danger">*</span>
                                </label>

                                <div class="d-flex flex-wrap gap-3">
                                    <div class="card cursor-pointer">
                                        <!-- ИП -->
                                        <label class="m-0">
                                        <div class="btn btn-block click-if-alone d-flex gap-2 align-items-center cursor-pointer">
                                            <input type="radio"
                                                id="org_type_ip"
                                                name="organization_type"
                                                value="1"
                                                class="custom-radio"
                                                required>
                                            <div class="fs-12">{{ translate('ip') }}</div>
                                        </div>
                                        </label>
                                    </div>

                                    <div class="card cursor-pointer">
                                        <!-- ООО -->
                                        <label class="m-0">
                                        <div class="btn btn-block click-if-alone d-flex gap-2 align-items-center cursor-pointer">
                                            <input type="radio"
                                                id="org_type_ooo"
                                                name="organization_type"
                                                value="2"
                                                class="custom-radio"
                                                required>
                                            <div class="fs-12">{{ translate('ooo') }}</div>
                                        </div>
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group mb-4">
                                <label  for="organization_name">{{translate('organization_name')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="organization_name" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="organization_oked">{{translate('organization_oked')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="organization_oked" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="bank_account_number">{{translate('bank_account_number')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="bank_account_number" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="bank_name">{{translate('bank_name')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="bank_name" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="bank_mfo_code">{{translate('bank_mfo_code')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="bank_mfo_code" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="identification_number">{{translate('identification_number')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="identification_number" placeholder="123456" required>
                            </div>

                            <div class="form-group mb-4">
                                <label  for="vat_percent">{{translate('vat_percent')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="vat_percent" placeholder="123456" required>
                            </div>

                            <!-- latitude -->
                            <div class="form-group mb-4">
                                <label for="latitude">{{translate('latitude')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="latitude" name="latitude" placeholder="41.3111" required>
                            </div>

                            <!-- longitude -->
                            <div class="form-group mb-4">
                                <label for="longitude">{{translate('longitude')}} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="longitude" name="longitude" placeholder="69.2797" required>
                            </div>

                            {{-- Карта для выбора координат --}}
                            @php($default_location = getWebConfig(name: 'default_location'))
                            @if(getWebConfig('map_api_status') == 1)
                            <div class="form-group">
                                <label class="mb-2">{{ translate('map') }}</label>
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

                            <div class="d-flex justify-content-between border p-3 p-xl-4 rounded mb-4">
                                <div class="d-flex flex-column gap-3 align-items-center">
                                    <div class="upload-file">
                                        <input type="file" class="upload-file__input" name="logo" accept="image/*" required>
                                        <div class="upload-file__img">
                                            <div class="temp-img-box">
                                                <div class="d-flex align-items-center flex-column gap-2">
                                                    <i class="tio-upload fs-30"></i>
                                                    <div class="fs-12 text-muted text-non-capitalize">{{translate('upload_file')}}</div>
                                                </div>
                                            </div>
                                            <img src="#" class="dark-support img-fit-contain border" alt="" hidden>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                        <h6 class="text-uppercase mb-1 fs-14">{{translate('upload_logo')}}</h6>
                                        <div class="text-muted text-non-capitalize fs-12">{{translate('image_ratio').' '.'1:1'}}</div>
                                        <div class="text-muted text-non-capitalize fs-12">{{translate('Image Size : Max 2 MB')}}</div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-3 align-items-center">
                                    <div class="upload-file">
                                        <input type="file" class="upload-file__input" name="banner" accept="image/*" required>
                                        <div class="upload-file__img style--two">
                                            <div class="temp-img-box">
                                                <div class="d-flex align-items-center flex-column gap-2">
                                                    <i class="tio-upload fs-30"></i>
                                                    <div class="fs-12 text-muted text-non-capitalize">{{translate('upload_file')}}</div>
                                                </div>
                                            </div>
                                            <img src="#" class="dark-support img-fit-contain border" alt="" hidden>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-1 upload-img-content text-center">
                                        <h6 class="text-uppercase mb-1 fs-14">{{translate('upload_banner')}}</h6>
                                        <div class="text-muted text-non-capitalize fs-12">{{translate('image_ratio').' '.'2:1'}}</div>
                                        <div class="text-muted text-non-capitalize fs-12">{{translate('Image Size : Max 2 MB')}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php($recaptcha = getWebConfig(name: 'recaptcha'))
                        @if(isset($recaptcha) && $recaptcha['status'] == 1)
                            <div id="recaptcha-element-vendor-register" class="w-100 pt-2" data-type="image"></div>
                        @else
                            <div class="mt-2">
                                <div class="row py-2">
                                    <div class="col-6 pr-0">
                                        <input type="text" class="form-control __h-40" name="default_recaptcha_id_seller_regi" id="default-recaptcha-id-vendor-register" value=""
                                               placeholder="{{translate('enter_captcha_value')}}" autocomplete="off" required>
                                    </div>
                                    <div class="col-6 input-icons mb-2 w-100 rounded bg-white">
                                    <span class="d-flex align-items-center align-items-center get-vendor-regi-recaptcha-verify"
                                          data-link="{{ route('vendor.auth.recaptcha', ['tmp'=>':dummy-id']) }}">
                                        <img src="{{ route('vendor.auth.recaptcha', ['tmp'=>1]).'?captcha_session_id=vendorRecaptchaSessionKey' }}" alt="" class="rounded __h-40" id="default_recaptcha_id">
                                        <i class="tio-refresh position-relative cursor-pointer p-2"></i>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="d-flex justify-content-start mt-2">
                            <label class="custom-checkbox align-items-center">
                                <input type="checkbox" class="" id="terms-checkbox" >
                                <span class="form-check-label">{{ translate('i_agree_with_the') }} <a
                                        href="{{ route('business-page.view', ['slug' => 'terms-and-conditions'])}}" target="_blank" class="text-underline color-bs-primary-force">
                                        {{ translate('terms_&_conditions') }}
                                    </a>
                                </span>
                            </label>
                        </div>
                        <div class="d-flex justify-content-end mb-2 gap-2">
                            <button type="button" class="btn btn-secondary back-to-main-page"> {{translate('back')}} </button>
                            <button type="button" class="btn btn--primary"  id="vendor-apply-submit" disabled="disabled"> {{translate('submit')}} </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.custom-radio {
    border: 1px solid #ddd !important;
    clip: auto !important;
    height: 1rem !important;
    position: static !important;
    width: 1rem !important;
}
</style>
@push('script')
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
