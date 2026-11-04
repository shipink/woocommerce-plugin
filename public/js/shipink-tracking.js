(function () {
  function init(root) {
    if (root.dataset.ready) return;
    root.dataset.ready = '1';

    var i18n = JSON.parse(root.querySelector('.shipink-tracking__i18n').textContent);
    var form = root.querySelector('.shipink-tracking__form');
    var message = root.querySelector('.shipink-tracking__message');
    var results = root.querySelector('.shipink-tracking__results');
    var button = form.querySelector('button');
    var dateFormat = new Intl.DateTimeFormat(document.documentElement.lang || undefined, {
      dateStyle: 'medium',
      timeStyle: 'short',
    });

    function show(text) {
      message.textContent = text || '';
      message.hidden = !text;
    }

    function el(tag, className, text) {
      var node = document.createElement(tag);
      if (className) node.className = className;
      if (text) node.textContent = text;
      return node;
    }

    function formatDate(value) {
      var date = value ? new Date(value) : null;
      return date && !isNaN(date) ? dateFormat.format(date) : '';
    }

    function statusLabel(code, incoming) {
      var key = incoming && i18n.status['return-' + code] ? 'return-' + code : code;
      return i18n.status[key] || i18n.status.unknown;
    }

    function render(shipments) {
      results.replaceChildren();
      shipments.forEach(function (s) {
        var incoming = s.direction === 'incoming';
        var status = s.status || {};
        var card = el('article', 'shipink-tracking__card');

        var head = el('div', 'shipink-tracking__card-head');
        head.append(el('strong', 'shipink-tracking__status shipink-tracking__status--' + (status.code || 'unknown'), statusLabel(status.code, incoming)));
        head.append(el('span', 'shipink-tracking__date', formatDate(status.date)));
        card.append(head);

        if (incoming) card.append(el('p', 'shipink-tracking__meta', i18n.return_shipment));
        if (s.tracking_number) card.append(el('p', 'shipink-tracking__meta', i18n.tracking_number + ': ' + s.tracking_number));

        var history = (s.history || []).filter(function (h) { return h && h.message; }).reverse();
        if (history.length) {
          var list = el('ol', 'shipink-tracking__history');
          history.forEach(function (h) {
            var item = el('li');
            item.append(el('span', 'shipink-tracking__date', formatDate(h.date)));
            item.append(el('span', null, h.message + (h.location ? ' · ' + h.location : '')));
            list.append(item);
          });
          card.append(list);
        }

        var link = el('a', 'shipink-tracking__link', i18n.details);
        if (/^https:/i.test(s.url || '')) link.href = s.url;
        link.target = '_blank';
        link.rel = 'noopener';
        card.append(link);

        results.append(card);
      });
    }

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var order = form.elements.order.value.trim();
      var contact = form.elements.contact.value.trim();
      if (!order || !contact) {
        show(i18n.invalid);
        return;
      }

      var params = new URLSearchParams({ shop: root.dataset.shop, order: order });
      params.set(contact.indexOf('@') === -1 ? 'phone' : 'email', contact);

      results.replaceChildren();
      show(i18n.loading);
      button.disabled = true;

      fetch(root.dataset.endpoint + '?' + params.toString())
        .then(function (response) {
          if (response.ok) return response.json();
          throw response.status;
        })
        .then(function (shipments) {
          show('');
          render(shipments);
        })
        .catch(function (status) {
          if (status === 404) show(i18n.not_found);
          else if (status === 400) show(i18n.invalid);
          else if (status === 429) show(i18n.too_many);
          else show(i18n.error);
        })
        .finally(function () {
          button.disabled = false;
        });
    });
  }

  function initAll() {
    document.querySelectorAll('.shipink-tracking').forEach(init);
  }

  initAll();
  document.addEventListener('shopify:section:load', initAll);
})();
