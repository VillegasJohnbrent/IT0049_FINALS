document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
            return;
        }
        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = 'true';
        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            button.disabled = true;
        });
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form').forEach((form) => {
        delete form.dataset.submitting;
        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            button.disabled = false;
        });
    });
});

const errors = document.getElementById('form-errors');
if (errors) errors.focus();

const product = document.getElementById('product_id');
const quantity = document.getElementById('quantity');

if (product && quantity) {
    const money = (centavos) => '₱' + (centavos / 100).toLocaleString('en-PH', {
        minimumFractionDigits: 2, maximumFractionDigits: 2,
    });

    function updateSummary() {
        const option = product.selectedOptions[0];
        const selected = Boolean(option && option.value);
        const parts = selected ? option.dataset.price.split('.') : ['0', '00'];
        const unit = Number(parts[0]) * 100 + Number(parts[1]);
        const count = Number(quantity.value);
        const valid = Number.isInteger(count) && count > 0 && count <= 1000000;
        const stock = selected ? Number(option.dataset.stock) : 0;

        document.getElementById('sale-product').textContent = selected ? option.dataset.name : 'No product selected';
        document.getElementById('unit-price').textContent = selected ? money(unit) : '—';
        document.getElementById('available-stock').textContent = selected ? stock : '—';
        document.getElementById('sale-quantity').textContent = valid ? count : '—';
        document.getElementById('sale-total').textContent = selected && valid ? money(unit * count) : '₱0.00';
        document.getElementById('stock-warning').hidden = !selected || !valid || count <= stock;

        const image = document.getElementById('sale-image');
        image.hidden = !selected || !option.dataset.image;
        if (!image.hidden) {
            image.src = option.dataset.image;
            image.alt = option.dataset.name;
        } else {
            image.removeAttribute('src');
            image.alt = '';
        }
    }

    product.addEventListener('change', updateSummary);
    quantity.addEventListener('input', updateSummary);
    updateSummary();
}
