// Shipink return block for merchant storefronts. The same file ships in the
// Shopify app (extensions/shipink/assets/returns.js, the source) and
// the WooCommerce plugin (public/js/shipink-returns.js, a copy): change it
// here, then copy it over. It talks to tracking-ui's /api/<channel>/return-requests
// (data-endpoint), which passes the requests on to return-api. The page and
// the copy follow tracking-ui's ReturnCard and ReturnForm.
(function () {
  'use strict';

  var MAX_PHOTOS = 3;
  // return-api's per-photo ceiling; a re-encoded photo aims well below it.
  var PHOTO_UPLOAD_LIMIT = 4 * 1024 * 1024 - 64 * 1024;
  var PHOTO_TARGET_BYTES = 3500000;
  var PHOTO_MAX_EDGE = 2048;
  var PHOTO_QUALITIES = [0.85, 0.7, 0.5];
  // return-api domain.Reasons, in its order.
  var REASONS = ['size_mismatch', 'damaged', 'wrong_item', 'not_as_expected', 'changed_mind', 'other'];
  var INSTRUCTION_CARRIERS = ['aras', 'mng', 'yurtici', 'surat', 'sendeo', 'ptt', 'kolay_gelsin', 'hepsijet', 'manual'];
  var WORDED_STATES = ['pending', 'shipped', 'received', 'accepted', 'refunded', 'expired'];

  var MESSAGES = {
    tr: {
      heading: 'İade talebi',
      orderNumber: 'Sipariş numarası',
      contact: 'E-posta veya telefon',
      find: 'Siparişimi bul',
      loading: 'Siparişiniz aranıyor…',
      notFound: 'Bu bilgilerle eşleşen bir sipariş bulunamadı. Sipariş numarasını ve siparişte kullandığınız e-posta ya da telefonu kontrol edin.',
      invalid: 'Sipariş numarasını ve geçerli bir e-posta ya da telefon girin.',
      tooMany: 'Çok fazla deneme yaptınız. Lütfen birkaç dakika sonra tekrar deneyin.',
      error: 'İade bilgileri şu an alınamıyor. Lütfen daha sonra tekrar deneyin.',
      shippedAt: '{date} tarihinde gönderildi',
      offerTitle: 'İade etmek mi istiyorsunuz?',
      windowEndsAt: '{date} tarihine kadar iade talebi oluşturabilirsiniz.',
      createButton: 'İade talebi oluştur',
      'status.pending': 'İade talebiniz inceleniyor',
      'status.pendingHint': 'Sonucu siparişinizi burada yeniden aratarak görebilirsiniz.',
      'status.approved': 'İade talebiniz onaylandı',
      'status.returnCode': 'İade kodu',
      'status.codeValidUntil': 'Kodunuz {date} tarihine kadar geçerlidir. Bu tarihe kadar kargoya teslim edilmeyen kod iptal edilir.',
      'status.customerNumber': 'Müşteri numarası',
      'status.carrier': 'Kargo firması: {carrier}',
      'status.trackReturn': 'İade gönderisini takip et',
      'status.rejected': 'İade talebiniz reddedildi',
      'status.shipped': 'İadeniz yolda',
      'status.shippedHint': 'Bize ulaştığında siparişinizi burada yeniden aratarak görebilirsiniz.',
      'status.received': 'İadeniz bize ulaştı',
      'status.receivedHint': 'Ürünler incelendikten sonra sizi bilgilendireceğiz.',
      'status.accepted': 'İadeniz kabul edildi',
      'status.acceptedHint': 'Para iadeniz yapılacak.',
      'status.refused': 'İadeniz kabul edilmedi',
      'status.refunded': 'Para iadeniz yapıldı',
      'status.refundedHint': 'Hesabınıza yansıması bankanıza göre birkaç gün sürebilir.',
      'status.expired': 'İade kodunuzun süresi doldu',
      'status.expiredHint': 'Ürünler süresi içinde kargoya verilmediği için iade kodu iptal edildi.',
      'status.sellerNote': 'Para iadesi notu: {note}',
      'status.rejectReason': 'Sebep: {reason}',
      'instructions.default': 'Paketinizi hazırlayıp iade kodunuzla birlikte kargo şubesine teslim edebilirsiniz.',
      'instructions.aras': 'Paketinizi herhangi bir Aras Kargo şubesine götürüp iade kodunuzu söylemeniz yeterli.',
      'instructions.mng': 'Paketinizi bir DHL eCommerce (MNG Kargo) şubesine götürün; iadeyi Shipink üzerinden yaptığınızı belirterek iade kodunu söyleyin.',
      'instructions.yurtici': 'Paketinizi herhangi bir Yurtiçi Kargo şubesine götürüp iade kodunuzu söylemeniz yeterli.',
      'instructions.yurticiWithCustomerNumber': 'Paketinizi herhangi bir Yurtiçi Kargo şubesine götürün; görevliye iade kodunuzu ve yukarıdaki müşteri numarasını verin.',
      'instructions.surat': 'Paketinizi herhangi bir Sürat Kargo şubesine götürüp iade kodunuzu söylemeniz yeterli.',
      'instructions.sendeo': '444 75 48 numaralı Sendeo Müşteri Hizmetleri\'ni arayın; iade kodunu referans numarası olarak vererek alım talebi oluşturun.',
      'instructions.ptt': 'Paketinizi bir PTT şubesine götürüp iade kodunu görevliye verin.',
      'instructions.pttWithCustomerNumber': 'Paketinizi bir PTT şubesine götürün; görevliye iade kodunuzu ve yukarıdaki müşteri numarasını verin.',
      'instructions.kolay_gelsin': 'Kolay Gelsin, paketi almak için sizi arayıp randevu verecek. Görevliye iade kodunuzu söylemeniz yeterli.',
      'instructions.hepsijet': 'HepsiJET, paketi almak için sizi arayıp randevu verecek. Görevliye iade kodunuzu söylemeniz yeterli.',
      'instructions.manual': 'İadeyi nasıl göndereceğinizi sizinle paylaşacağız.',
      'form.items': 'İade etmek istediğiniz ürünleri seçin',
      'form.quantity': 'Adet',
      'form.reason': 'İade sebebi',
      'form.reasonPlaceholder': 'Sebep seçin',
      'form.note': 'Açıklama (isteğe bağlı)',
      'form.notePlaceholder': 'Eklemek istediğiniz bir ayrıntı varsa yazın',
      'form.photos': 'Fotoğraflar (isteğe bağlı, en fazla 3)',
      'form.addPhoto': 'Fotoğraf ekle',
      'form.uploading': 'Yükleniyor…',
      'form.removePhoto': '{n}. fotoğrafı kaldır',
      'form.verifyPhone': 'Siparişteki telefon numaranızın son 4 hanesi',
      'form.verifyEmail': 'Siparişteki e-posta adresiniz',
      'form.verifyHint': 'Talebin size ait olduğunu doğrulamak için bu bilgiyi istiyoruz. Deneme hakkınız sınırlı olduğundan lütfen dikkatle girin.',
      'form.submit': 'Talebi gönder',
      'form.submitting': 'Gönderiliyor…',
      'form.cancel': 'Vazgeç',
      'form.selectItem': 'En az bir ürün seçin.',
      'form.selectReason': 'Seçtiğiniz her ürün için iade sebebi belirtin.',
      'form.enterPhone': 'Telefon numaranızın son 4 hanesini girin.',
      'form.enterEmail': 'E-posta adresinizi girin.',
      'reasons.size_mismatch': 'Beden/ölçü uymadı',
      'reasons.damaged': 'Hasarlı geldi',
      'reasons.wrong_item': 'Yanlış ürün gönderildi',
      'reasons.not_as_expected': 'Beklentimi karşılamadı',
      'reasons.changed_mind': 'Vazgeçtim',
      'reasons.other': 'Diğer',
      'ineligible.notDelivered': 'Gönderiniz teslim edildikten sonra iade talebi oluşturabilirsiniz.',
      'ineligible.expired': 'Bu gönderinin iade süresi doldu.',
      'ineligible.alreadyRequested': 'Bu gönderideki ürünler için zaten iade talebi oluşturulmuş.',
      'ineligible.contactSeller': 'Bu gönderi için iade talebi buradan oluşturulamıyor. İade için lütfen bizimle iletişime geçin.',
      'errors.verificationFailedPhone': 'Girdiğiniz 4 hane siparişteki telefon numarasıyla eşleşmedi.',
      'errors.verificationFailedEmail': 'Girdiğiniz e-posta adresi siparişteki adresle eşleşmedi.',
      'errors.verificationLocked': 'Çok fazla hatalı deneme yapıldı. Bir saat sonra yeniden deneyebilirsiniz.',
      'errors.verificationExhausted': 'Bu gönderi için doğrulama deneme hakkınız doldu. İade için lütfen bizimle iletişime geçin.',
      'errors.unitsUnavailable': 'Seçtiğiniz ürünler için zaten bir iade talebi var.',
      'errors.createInProgress': 'Talebiniz şu anda işleniyor. Birkaç saniye sonra siparişinizi yeniden arayın.',
      'errors.selectionChanged': 'Seçiminiz güncel değil. Siparişinizi yeniden arayıp tekrar deneyin.',
      'errors.tokenExpired': 'Bu sayfa uzun süredir açık. Siparişinizi yeniden arayıp tekrar deneyin.',
      'errors.photoUnsupported': 'Bu dosya türü desteklenmiyor. JPEG, PNG, WebP veya HEIC biçiminde bir fotoğraf seçin.',
      'errors.photoTooLarge': 'Fotoğraf çok büyük. Daha küçük bir fotoğraf seçin.',
      'errors.photoLimit': 'Bu gönderi için bugünkü fotoğraf yükleme sınırına ulaşıldı. Talebinizi fotoğrafsız gönderebilirsiniz.',
      'errors.rateLimited': 'Çok fazla deneme yapıldı. Bir süre sonra yeniden deneyin.',
      'errors.generic': 'Bir sorun oluştu. Lütfen biraz sonra yeniden deneyin.'
    },
    en: {
      heading: 'Return request',
      orderNumber: 'Order number',
      contact: 'Email or phone',
      find: 'Find my order',
      loading: 'Looking up your order…',
      notFound: 'No order matches these details. Check the order number and the email or phone you used for the order.',
      invalid: 'Enter the order number and a valid email or phone.',
      tooMany: 'Too many attempts. Please try again in a few minutes.',
      error: 'Return details are unavailable right now. Please try again later.',
      shippedAt: 'Shipped on {date}',
      offerTitle: 'Want to return something?',
      windowEndsAt: 'You can request a return until {date}.',
      createButton: 'Request a return',
      'status.pending': 'Your return request is under review',
      'status.pendingHint': 'Look up your order here again to see the outcome.',
      'status.approved': 'Your return request was approved',
      'status.returnCode': 'Return code',
      'status.codeValidUntil': 'Your code is valid until {date}. An unused code is canceled after that date.',
      'status.customerNumber': 'Customer number',
      'status.carrier': 'Carrier: {carrier}',
      'status.trackReturn': 'Track the return shipment',
      'status.rejected': 'Your return request was declined',
      'status.shipped': 'Your return is on its way',
      'status.shippedHint': 'Look up your order here again to see when it reaches us.',
      'status.received': 'Your return has reached us',
      'status.receivedHint': 'We will notify you once the items have been inspected.',
      'status.accepted': 'Your return was accepted',
      'status.acceptedHint': 'Your refund will be issued.',
      'status.refused': 'Your return was not accepted',
      'status.refunded': 'Your refund has been issued',
      'status.refundedHint': 'Depending on your bank, it may take a few days to show in your account.',
      'status.expired': 'Your return code has expired',
      'status.expiredHint': 'The items were not handed to the carrier in time, so the return code was canceled.',
      'status.sellerNote': 'Refund note: {note}',
      'status.rejectReason': 'Reason: {reason}',
      'instructions.default': 'Pack the item and hand it in at the carrier\'s branch with your return code.',
      'instructions.aras': 'Take the package to any Aras Kargo branch and give them your return code.',
      'instructions.mng': 'Take the package to a DHL eCommerce (MNG Kargo) branch, say it is a return made through Shipink and give them your return code.',
      'instructions.yurtici': 'Take the package to any Yurtiçi Kargo branch and give them your return code.',
      'instructions.yurticiWithCustomerNumber': 'Take the package to any Yurtiçi Kargo branch and give them your return code and the customer number shown above.',
      'instructions.surat': 'Take the package to any Sürat Kargo branch and give them your return code.',
      'instructions.sendeo': 'Call Sendeo Customer Services on 444 75 48 and give your return code as the reference number to book a pickup.',
      'instructions.ptt': 'Take the package to a PTT branch and give them your return code.',
      'instructions.pttWithCustomerNumber': 'Take the package to a PTT branch and give them your return code and the customer number shown above.',
      'instructions.kolay_gelsin': 'Kolay Gelsin will call you to schedule a pickup. Give your return code to the driver when they arrive.',
      'instructions.hepsijet': 'HepsiJET will call you to schedule a pickup. Give your return code to the driver when they arrive.',
      'instructions.manual': 'We will tell you how to send the return.',
      'form.items': 'Choose the items you want to return',
      'form.quantity': 'Quantity',
      'form.reason': 'Reason for return',
      'form.reasonPlaceholder': 'Choose a reason',
      'form.note': 'Details (optional)',
      'form.notePlaceholder': 'Anything you would like to add',
      'form.photos': 'Photos (optional, up to 3)',
      'form.addPhoto': 'Add photo',
      'form.uploading': 'Uploading…',
      'form.removePhoto': 'Remove photo {n}',
      'form.verifyPhone': 'Last 4 digits of the phone number on the order',
      'form.verifyEmail': 'Email address on the order',
      'form.verifyHint': 'We ask to confirm the request is yours. The number of attempts is limited, so please enter it carefully.',
      'form.submit': 'Send request',
      'form.submitting': 'Sending…',
      'form.cancel': 'Cancel',
      'form.selectItem': 'Choose at least one item.',
      'form.selectReason': 'Choose a reason for every item you selected.',
      'form.enterPhone': 'Enter the last 4 digits of your phone number.',
      'form.enterEmail': 'Enter your email address.',
      'reasons.size_mismatch': 'Wrong size or fit',
      'reasons.damaged': 'Arrived damaged',
      'reasons.wrong_item': 'Wrong item sent',
      'reasons.not_as_expected': 'Not what I expected',
      'reasons.changed_mind': 'Changed my mind',
      'reasons.other': 'Other',
      'ineligible.notDelivered': 'You can request a return once your shipment has been delivered.',
      'ineligible.expired': 'The return period for this shipment has ended.',
      'ineligible.alreadyRequested': 'A return has already been requested for the items in this shipment.',
      'ineligible.contactSeller': 'A return for this shipment cannot be requested here. Please contact us.',
      'errors.verificationFailedPhone': 'The 4 digits you entered do not match the phone number on the order.',
      'errors.verificationFailedEmail': 'The email address you entered does not match the one on the order.',
      'errors.verificationLocked': 'Too many incorrect attempts. You can try again in an hour.',
      'errors.verificationExhausted': 'No verification attempts are left for this shipment. Please contact us about your return.',
      'errors.unitsUnavailable': 'A return request already exists for the items you selected.',
      'errors.createInProgress': 'Your request is being processed. Look up your order again in a few seconds.',
      'errors.selectionChanged': 'Your selection is out of date. Look up your order again and retry.',
      'errors.tokenExpired': 'This page has been open for a while. Look up your order again and retry.',
      'errors.photoUnsupported': 'This file type is not supported. Choose a JPEG, PNG, WebP or HEIC photo.',
      'errors.photoTooLarge': 'The photo is too large. Choose a smaller one.',
      'errors.photoLimit': 'Today\'s photo upload limit for this shipment has been reached. You can send your request without photos.',
      'errors.rateLimited': 'Too many attempts. Try again in a few minutes.',
      'errors.generic': 'Something went wrong. Please try again in a moment.'
    }
  };

  // The contact the buyer found the order with, as return-api's verification
  // answer when it is the kind asked for; '' means the form must ask.
  function verificationFor(method, contact) {
    var value = String(contact || '').trim();
    var isEmail = value.indexOf('@') !== -1;
    if (method === 'email') return isEmail ? value.toLowerCase() : '';
    if (method === 'phone_last4') return isEmail ? '' : lastFourDigits(value);
    return '';
  }

  function lastFourDigits(value) {
    return String(value).replace(/\D/g, '').slice(-4);
  }

  // The buyer asked to return, so every reason gets a message (the tracking
  // page stays silent for some). not_found and not_outgoing never get here:
  // the lookup leaves those shipments out.
  function ineligibleGroup(reason) {
    switch (reason) {
      case 'not_delivered': return 'notDelivered';
      case 'window_expired': return 'expired';
      case 'request_exists': return 'alreadyRequested';
      default: return 'contactSeller';
    }
  }

  // return-api answers nothing_returnable once a non-open request holds every
  // unit; the request card already says what happened.
  function noteGroup(reason, hasRequest) {
    if (hasRequest && (reason === 'request_exists' || reason === 'nothing_returnable')) return '';
    return ineligibleGroup(reason);
  }

  // As tracking-ui's errorMessageKey, without reCAPTCHA.
  function errorKey(code, message, verification) {
    switch (code) {
      case 'RETURN_VERIFICATION_FAILED':
        return verification === 'email' ? 'errors.verificationFailedEmail' : 'errors.verificationFailedPhone';
      case 'RETURN_VERIFICATION_LOCKED': return 'errors.verificationLocked';
      case 'RETURN_VERIFICATION_EXHAUSTED': return 'errors.verificationExhausted';
      case 'RETURN_UNITS_UNAVAILABLE': return 'errors.unitsUnavailable';
      case 'RETURN_CREATE_IN_PROGRESS': return 'errors.createInProgress';
      case 'RETURN_NOT_ELIGIBLE': return 'ineligible.' + ineligibleGroup(message);
      case 'BAD_REQUEST': return 'errors.selectionChanged';
      // The lookup's token expired (it lives an hour) or was never issued.
      case 'RETURN_TOKEN_INVALID': return 'errors.tokenExpired';
      case 'RETURN_PHOTO_UNSUPPORTED': return 'errors.photoUnsupported';
      case 'RETURN_PHOTO_LIMIT': return 'errors.photoLimit';
      case 'PHOTO_TOO_LARGE': return 'errors.photoTooLarge';
      case 'RATE_LIMITED': return 'errors.rateLimited';
      default: return 'errors.generic';
    }
  }

  function instructionKey(carrierID, customerNumber) {
    if ((carrierID === 'ptt' || carrierID === 'yurtici') && customerNumber) {
      return 'instructions.' + carrierID + 'WithCustomerNumber';
    }
    return 'instructions.' + (INSTRUCTION_CARRIERS.indexOf(carrierID) >= 0 ? carrierID : 'default');
  }

  // processing (and any state this build does not know) reads as pending.
  function cardState(status) {
    return WORDED_STATES.indexOf(status) >= 0 ? status : 'pending';
  }

  function fitWithin(width, height, maxEdge) {
    var scale = Math.min(1, maxEdge / Math.max(width, height));
    return { width: Math.round(width * scale), height: Math.round(height * scale) };
  }

  // Re-encodes a photo as a JPEG small enough for return-api, which also drops
  // its EXIF (location included). A photo the browser cannot decode (HEIC
  // outside Safari) goes as it is when it fits; null means it cannot go.
  async function preparePhoto(file) {
    var fallback = file.size <= PHOTO_UPLOAD_LIMIT ? file : null;
    var source = await decodeImage(file);
    if (!source) return fallback;

    var size = fitWithin(source.width, source.height, PHOTO_MAX_EDGE);
    var canvas = document.createElement('canvas');
    canvas.width = size.width;
    canvas.height = size.height;
    var context = canvas.getContext('2d');
    if (!context) {
      source.release();
      return fallback;
    }
    // JPEG has no alpha: transparent pixels would turn black.
    context.fillStyle = '#fff';
    context.fillRect(0, 0, size.width, size.height);
    context.drawImage(source.image, 0, 0, size.width, size.height);
    source.release();

    var result = null;
    var encoded = false;
    for (var i = 0; i < PHOTO_QUALITIES.length; i++) {
      var blob = await new Promise(function (resolve) { canvas.toBlob(resolve, 'image/jpeg', PHOTO_QUALITIES[i]); });
      if (blob) {
        encoded = true;
        if (blob.size <= PHOTO_TARGET_BYTES) {
          result = blob;
          break;
        }
      }
    }
    canvas.width = 0;
    canvas.height = 0;
    return result || (encoded ? null : fallback);
  }

  // Applies the EXIF orientation. createImageBitmap may be missing or reject
  // the options object (TypeError); then an <img> decodes.
  async function decodeImage(file) {
    if (typeof createImageBitmap === 'function') {
      var attempts = [{ imageOrientation: 'from-image' }, undefined];
      for (var i = 0; i < attempts.length; i++) {
        try {
          var bitmap = await createImageBitmap(file, attempts[i]);
          return { image: bitmap, width: bitmap.width, height: bitmap.height, release: function () { bitmap.close(); } };
        } catch (error) {
          if (!(error instanceof TypeError) || !attempts[i]) break;
        }
      }
      return null;
    }
    var url = URL.createObjectURL(file);
    try {
      var img = new Image();
      await new Promise(function (resolve, reject) {
        img.onload = resolve;
        img.onerror = reject;
        img.src = url;
      });
      return { image: img, width: img.naturalWidth, height: img.naturalHeight, release: function () { URL.revokeObjectURL(url); } };
    } catch (error) {
      URL.revokeObjectURL(url);
      return null;
    }
  }

  function el(tag, className, text) {
    var node = document.createElement(tag);
    if (className) node.className = className;
    if (text) node.textContent = text;
    return node;
  }

  function field(label, control) {
    var node = el('label', 'shipink-returns__field');
    node.append(el('span', null, label), control);
    return node;
  }

  function option(value, text) {
    var node = el('option', null, text);
    node.value = value;
    return node;
  }

  var fieldCount = 0;

  function errorNode() {
    var node = el('p', 'shipink-returns__error');
    node.setAttribute('role', 'alert');
    node.hidden = true;
    return node;
  }

  function init(root) {
    if (root.dataset.ready) return;
    root.dataset.ready = '1';

    var lang = /^tr/i.test(root.dataset.locale || document.documentElement.lang || '') ? 'tr' : 'en';
    var endpoint = root.dataset.endpoint;
    var dayFormat = new Intl.DateTimeFormat(lang, { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Europe/Istanbul' });

    function t(key, vars) {
      var text = MESSAGES[lang][key] || MESSAGES.en[key] || key;
      return text.replace(/\{(\w+)\}/g, function (_, name) {
        return vars && vars[name] != null ? String(vars[name]) : '';
      });
    }

    function formatDay(value) {
      var date = value ? new Date(value) : null;
      return date && !isNaN(date) ? dayFormat.format(date) : '';
    }

    // POSTs to the block's endpoint; resolves to the envelope's data, rejects
    // with its meta ({ code, message }), or with {} when offline.
    function post(path, body, headers) {
      return fetch(endpoint + path, { method: 'POST', body: body, headers: headers })
        .catch(function () { throw {}; })
        .then(function (response) {
          return response.json().catch(function () { return null; }).then(function (data) {
            if (response.ok && data && data.success) return data.data;
            throw (data && data.meta) || { code: response.status === 429 ? 'RATE_LIMITED' : '' };
          });
        });
    }

    var orderInput = el('input');
    orderInput.type = 'text';
    orderInput.required = true;
    orderInput.autocomplete = 'off';
    orderInput.placeholder = '#1001';
    var contactInput = el('input');
    contactInput.type = 'text';
    contactInput.required = true;
    contactInput.autocomplete = 'email';
    var findButton = el('button', 'shipink-returns__button', t('find'));
    findButton.type = 'submit';
    var lookup = el('form', 'shipink-returns__lookup');
    lookup.noValidate = true;
    lookup.append(field(t('orderNumber'), orderInput), field(t('contact'), contactInput), findButton);
    var message = el('p', 'shipink-returns__message');
    message.setAttribute('role', 'status');
    message.setAttribute('aria-live', 'polite');
    message.hidden = true;
    var results = el('div', 'shipink-returns__results');
    root.replaceChildren(el('h2', 'shipink-returns__heading', root.dataset.heading || t('heading')), lookup, message, results);

    function show(text) {
      message.textContent = text || '';
      message.hidden = !text;
    }

    lookup.addEventListener('submit', function (event) {
      event.preventDefault();
      var order = orderInput.value.trim();
      var contact = contactInput.value.trim();
      if (!order || !contact) {
        show(t('invalid'));
        return;
      }
      var params = new URLSearchParams({ shop: root.dataset.shop, order: order });
      params.set(contact.indexOf('@') === -1 ? 'phone' : 'email', contact);

      results.replaceChildren();
      show(t('loading'));
      findButton.disabled = true;
      fetch(endpoint + '?' + params.toString())
        .then(function (response) {
          if (response.ok) return response.json();
          throw response.status;
        })
        .then(function (entries) {
          show('');
          entries.forEach(function (entry) { results.append(card(entry, contact)); });
        })
        .catch(function (status) {
          if (status === 404) show(t('notFound'));
          else if (status === 400) show(t('invalid'));
          else if (status === 429) show(t('tooMany'));
          else show(t('error'));
        })
        .finally(function () {
          findButton.disabled = false;
        });
    });

    function trackLink(view) {
      if (!/^https:/i.test(view.return_tracking_url || '')) return null;
      var link = el('a', 'shipink-returns__link', t('status.trackReturn'));
      link.href = view.return_tracking_url;
      link.target = '_blank';
      link.rel = 'noopener';
      return link;
    }

    function statusBlock(view) {
      var box = el('div', 'shipink-returns__status');
      var link = null;
      if (view.status === 'approved') {
        box.append(el('p', 'shipink-returns__title', t('status.approved')));
        var code = view.return_code || view.return_tracking_id || '';
        if (code) {
          box.append(el('p', 'shipink-returns__muted', t('status.returnCode')), el('p', 'shipink-returns__code', code));
          var until = formatDay(view.code_valid_until);
          if (until) box.append(el('p', 'shipink-returns__muted', t('status.codeValidUntil', { date: until })));
        }
        if (view.return_customer_number) {
          box.append(el('p', 'shipink-returns__muted', t('status.customerNumber')), el('p', 'shipink-returns__code', view.return_customer_number));
        }
        if (view.return_carrier_name) box.append(el('p', null, t('status.carrier', { carrier: view.return_carrier_name })));
        box.append(el('p', null, t(instructionKey(view.return_carrier_id, view.return_customer_number))));
        link = trackLink(view);
      } else if (view.status === 'rejected' || view.status === 'refused') {
        box.append(el('p', 'shipink-returns__title', t('status.' + view.status)));
        var reason = view.reject_reason || view.refuse_reason;
        if (reason) box.append(el('p', null, t('status.rejectReason', { reason: reason })));
      } else {
        var state = cardState(view.status);
        box.append(el('p', 'shipink-returns__title', t('status.' + state)), el('p', 'shipink-returns__muted', t('status.' + state + 'Hint')));
        if (view.status === 'refunded' && view.refund_note) box.append(el('p', null, t('status.sellerNote', { note: view.refund_note })));
        if (view.status === 'shipped') link = trackLink(view);
      }
      if (link) box.append(link);
      return box;
    }

    function card(entry, contact) {
      var node = el('article', 'shipink-returns__card');

      // focusTarget: 'create' puts focus back on the create button, 'status' on the outcome's title.
      function render(focusTarget) {
        node.replaceChildren();
        var head = el('div', 'shipink-returns__card-head');
        head.append(el('strong', null, entry.carrier_name || ''));
        var shipped = formatDay(entry.shipped_at);
        if (shipped) head.append(el('span', 'shipink-returns__muted', t('shippedAt', { date: shipped })));
        node.append(head);

        if (entry.request) node.append(statusBlock(entry.request));

        var offer = entry.eligibility;
        if (offer && offer.eligible) {
          var box = el('div', 'shipink-returns__offer');
          box.append(el('p', 'shipink-returns__title', t('offerTitle')));
          var ends = formatDay(offer.window_ends_at);
          if (ends) box.append(el('p', 'shipink-returns__muted', t('windowEndsAt', { date: ends })));
          var open = el('button', 'shipink-returns__button', t('createButton'));
          open.type = 'button';
          open.addEventListener('click', function () {
            var form = returnForm(entry, contact, onCreated, function () { render('create'); });
            box.replaceWith(form);
            (form.querySelector('input[type="checkbox"]') || form.querySelector('textarea')).focus();
          });
          box.append(open);
          node.append(box);
        } else if (offer) {
          var group = noteGroup(offer.reason, !!entry.request);
          if (group) {
            node.append(el('p', 'shipink-returns__note', t('ineligible.' + group)));
          }
        }

        if (focusTarget === 'create' && open) open.focus();
        if (focusTarget === 'status') {
          var title = node.querySelector('.shipink-returns__status .shipink-returns__title');
          if (title) {
            title.tabIndex = -1;
            title.focus();
          }
        }
      }

      function onCreated(view) {
        entry.request = view;
        entry.eligibility = null;
        render('status');
      }

      render();
      return node;
    }

    function returnForm(entry, contact, onCreated, onCancel) {
      var offer = entry.eligibility;
      var isPhone = offer.verification !== 'email';
      var derived = verificationFor(offer.verification, contact);
      var photos = [];
      var uploading = false;
      var submitting = false;
      var locked = false;

      var form = el('form', 'shipink-returns__form');
      form.noValidate = true;
      var itemsError = errorNode();
      var photoError = errorNode();
      var verifyError = errorNode();
      var formError = errorNode();

      var lines = offer.items.map(function (item) {
        var check = el('input');
        check.type = 'checkbox';
        // A single item starts selected.
        check.checked = offer.items.length === 1;
        var quantity = el('select');
        for (var n = 1; n <= item.returnable_quantity; n++) quantity.append(option(String(n), String(n)));
        var reason = el('select');
        var placeholder = option('', t('form.reasonPlaceholder'));
        placeholder.disabled = true;
        placeholder.selected = true;
        reason.append(placeholder);
        REASONS.forEach(function (r) { reason.append(option(r, t('reasons.' + r))); });

        var head = el('label', 'shipink-returns__item-head');
        head.append(check);
        if (/^https:/i.test(item.image || '')) {
          var img = el('img');
          img.src = item.image;
          img.alt = '';
          head.append(img);
        }
        head.append(el('span', null, item.name));
        var details = el('div', 'shipink-returns__item-details');
        details.append(field(t('form.quantity'), quantity), field(t('form.reason'), reason));
        details.hidden = !check.checked;
        check.addEventListener('change', function () { details.hidden = !check.checked; });
        var row = el('div', 'shipink-returns__item');
        row.append(head, details);
        return { item: item, check: check, quantity: quantity, reason: reason, row: row };
      });

      var note = el('textarea');
      note.maxLength = 1000;
      note.rows = 3;
      note.placeholder = t('form.notePlaceholder');

      var photoList = el('div', 'shipink-returns__photos');
      var fileInput = el('input');
      fileInput.type = 'file';
      fileInput.accept = 'image/*';
      var addText = el('span', null, t('form.addPhoto'));
      var addLabel = el('label', 'shipink-returns__photo-add');
      addLabel.append(addText, fileInput);

      var verifyInput = el('input');
      verifyInput.type = isPhone ? 'text' : 'email';
      verifyInput.autocomplete = isPhone ? 'off' : 'email';
      if (isPhone) verifyInput.inputMode = 'numeric';
      verifyInput.value = derived;
      // The hint and error sit beside the label, not in it, so they stay out of the input's name.
      var hint = el('span', 'shipink-returns__muted', t('form.verifyHint'));
      var uid = 'shipink-returns-' + (++fieldCount);
      hint.id = uid + '-hint';
      verifyError.id = uid + '-error';
      verifyInput.setAttribute('aria-describedby', hint.id + ' ' + verifyError.id);
      var verifyLabel = el('label');
      verifyLabel.append(el('span', null, t(isPhone ? 'form.verifyPhone' : 'form.verifyEmail')), verifyInput);
      var verifyField = el('div', 'shipink-returns__field');
      verifyField.append(verifyLabel, hint, verifyError);
      // The contact the order was found with already answers it.
      verifyField.hidden = !!derived;

      // Only exactly four digits ever reach return-api. Typing a fifth digit
      // changes nothing; a pasted (or autofilled) number counts from its end.
      var lastVerify = derived;
      verifyInput.addEventListener('input', function (event) {
        if (!isPhone) return;
        var digits = verifyInput.value.replace(/\D/g, '');
        var typed = (event.inputType === 'insertText' || event.inputType === 'insertCompositionText') && digits.length - lastVerify.length <= 1;
        lastVerify = verifyInput.value = typed ? digits.slice(0, 4) : lastFourDigits(digits);
      });

      var submit = el('button', 'shipink-returns__button', t('form.submit'));
      submit.type = 'submit';
      var cancel = el('button', 'shipink-returns__secondary', t('form.cancel'));
      cancel.type = 'button';
      cancel.addEventListener('click', function () {
        cleanup();
        onCancel();
      });

      var itemsBox = el('div', 'shipink-returns__section');
      itemsBox.append(el('p', 'shipink-returns__title', t('form.items')));
      lines.forEach(function (line) { itemsBox.append(line.row); });
      itemsBox.append(itemsError);
      var photosBox = el('div', 'shipink-returns__section');
      photosBox.append(el('p', 'shipink-returns__title', t('form.photos')), photoList, photoError);
      var actions = el('div', 'shipink-returns__actions');
      actions.append(submit, cancel);
      form.append(itemsBox, field(t('form.note'), note), photosBox, verifyField, formError, actions);

      function cleanup() {
        photos.forEach(function (photo) { URL.revokeObjectURL(photo.preview); });
        photos = [];
      }

      function sync() {
        addText.textContent = uploading ? t('form.uploading') : t('form.addPhoto');
        fileInput.disabled = uploading;
        submit.disabled = uploading || submitting || locked;
        submit.textContent = submitting ? t('form.submitting') : t('form.submit');
        cancel.disabled = submitting;
      }

      function drawPhotos() {
        photoList.replaceChildren();
        photos.forEach(function (photo, index) {
          var item = el('div', 'shipink-returns__photo');
          var img = el('img');
          img.src = photo.preview;
          img.alt = '';
          var remove = el('button', 'shipink-returns__photo-remove', '✕');
          remove.type = 'button';
          remove.setAttribute('aria-label', t('form.removePhoto', { n: index + 1 }));
          remove.addEventListener('click', function () {
            URL.revokeObjectURL(photo.preview);
            photos.splice(index, 1);
            drawPhotos();
          });
          item.append(img, remove);
          photoList.append(item);
        });
        if (photos.length < MAX_PHOTOS) photoList.append(addLabel);
      }

      // Clears every message first and sets the text a frame later, so the same
      // one twice is announced twice.
      function showError(node, text) {
        [itemsError, photoError, verifyError, formError].forEach(function (n) {
          n.textContent = '';
          n.hidden = true;
        });
        if (node === verifyError) verifyField.hidden = false;
        requestAnimationFrame(function () {
          node.textContent = text;
          node.hidden = false;
          node.scrollIntoView({ block: 'nearest' });
        });
      }

      function fail(meta, fallback) {
        var code = (meta && meta.code) || '';
        var text = t(errorKey(code, meta && meta.message, offer.verification));
        // Out of attempts, or no longer eligible: the message replaces the form.
        if (code === 'RETURN_VERIFICATION_EXHAUSTED' || code === 'RETURN_NOT_ELIGIBLE') {
          cleanup();
          form.replaceChildren(el('p', 'shipink-returns__note', text));
          return;
        }
        if (code === 'RETURN_VERIFICATION_LOCKED') {
          locked = true;
          sync();
        }
        showError(code.indexOf('RETURN_VERIFICATION') === 0 ? verifyError : fallback, text);
      }

      fileInput.addEventListener('change', async function () {
        var file = fileInput.files && fileInput.files[0];
        fileInput.value = '';
        if (!file || uploading || photos.length >= MAX_PHOTOS) return;
        uploading = true;
        sync();
        try {
          var photo = await preparePhoto(file);
          if (!photo) {
            showError(photoError, t('errors.photoTooLarge'));
            return;
          }
          var body = new FormData();
          body.append('photo', photo, 'photo');
          var data = await post('/photos?tracking_id=' + encodeURIComponent(entry.tracking_id) + '&token=' + encodeURIComponent(entry.return_token || ''), body);
          // The form was closed meanwhile: nobody is left to revoke a preview.
          if (!form.isConnected) return;
          photoError.hidden = true;
          photos.push({ key: data.key, preview: URL.createObjectURL(photo) });
          drawPhotos();
        } catch (meta) {
          fail(meta, photoError);
        } finally {
          uploading = false;
          sync();
        }
      });

      form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (submitting || uploading || locked) return;
        var chosen = lines.filter(function (line) { return line.check.checked; });
        if (chosen.length === 0) return showError(itemsError, t('form.selectItem'));
        if (chosen.some(function (line) { return !line.reason.value; })) return showError(itemsError, t('form.selectReason'));
        var verification = verifyInput.value.trim();
        if (isPhone ? !/^\d{4}$/.test(verification) : !/^\S+@\S+$/.test(verification)) {
          return showError(verifyError, t(isPhone ? 'form.enterPhone' : 'form.enterEmail'));
        }

        submitting = true;
        sync();
        try {
          var view = await post('', JSON.stringify({
            tracking_id: entry.tracking_id,
            return_token: entry.return_token || '',
            verification: verification,
            // sales_channel_item_id goes back exactly as eligibility sent it:
            // it is the line's key in return-api (empty means order_item_id).
            items: chosen.map(function (line) {
              return {
                order_item_id: line.item.order_item_id,
                sales_channel_item_id: line.item.sales_channel_item_id || '',
                quantity: Number(line.quantity.value),
                reason: line.reason.value
              };
            }),
            note: note.value.trim(),
            photo_keys: photos.map(function (photo) { return photo.key; })
          }), { 'content-type': 'application/json' });
          cleanup();
          onCreated(view);
        } catch (meta) {
          fail(meta, formError);
        } finally {
          submitting = false;
          sync();
        }
      });

      drawPhotos();
      sync();
      return form;
    }
  }

  function initAll() {
    document.querySelectorAll('.shipink-returns').forEach(init);
  }

  window.ShipinkReturns = {
    verificationFor: verificationFor,
    ineligibleGroup: ineligibleGroup,
    noteGroup: noteGroup,
    errorKey: errorKey,
    instructionKey: instructionKey,
    cardState: cardState,
    lastFourDigits: lastFourDigits,
    fitWithin: fitWithin,
    messages: MESSAGES
  };

  initAll();
  document.addEventListener('shopify:section:load', initAll);
})();
