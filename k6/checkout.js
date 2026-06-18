import http from 'k6/http';
import { check, sleep } from 'k6';

// Load test: guest cart + COD checkout flow.
// Requires seeded catalogue + a shipping method with code "standard".
// Run: k6 run -e BASE_URL=https://staging.ranga.test -e PRODUCT_ID=... -e VARIANT_ID=... k6/checkout.js

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const PRODUCT_ID = __ENV.PRODUCT_ID;
const VARIANT_ID = __ENV.VARIANT_ID;

export const options = {
    stages: [
        { duration: '30s', target: 20 },
        { duration: '1m', target: 20 },
        { duration: '20s', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<800'],
        http_req_failed: ['rate<0.02'],
    },
};

export default function () {
    const token = `k6-${__VU}-${__ITER}`;
    const headers = { 'X-Cart-Token': token, 'Content-Type': 'application/json', Accept: 'application/json' };

    const add = http.post(`${BASE_URL}/api/v1/cart/items`, JSON.stringify({
        product_id: PRODUCT_ID,
        variant_id: VARIANT_ID,
        quantity: 1,
    }), { headers });
    check(add, { 'add to cart 201': (r) => r.status === 201 });

    const checkout = http.post(`${BASE_URL}/api/v1/checkout`, JSON.stringify({
        shipping: { name: 'Load Test', phone: '01700000000', address_line_1: 'Rd 1', city: 'Dhaka' },
        shipping_method: 'standard',
        payment_gateway: 'cod',
        guest_email: `${token}@example.com`,
    }), { headers });
    check(checkout, { 'checkout 201': (r) => r.status === 201 });

    sleep(1);
}
