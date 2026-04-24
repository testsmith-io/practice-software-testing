// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

import {test, expect} from '@playwright/test';
import {faker} from '@faker-js/faker';

test.describe('Checkout Feature', () => {

    test('Checkout_billingAddressStep_houseNumberIsPrefilled', async ({page}) => {
        const email = faker.internet.email().toLowerCase();
        const password = `${faker.internet.password({length: 10})}Aa1!`;
        const street = faker.location.street();
        const houseNumber = faker.location.buildingNumber();

        await page.goto('/auth/register');
        await page.locator('[data-test="first-name"]').fill(faker.person.firstName());
        await page.locator('[data-test="last-name"]').fill(faker.person.lastName());
        await page.locator('[data-test="dob"]').fill('1990-01-15');
        await page.locator('[data-test="country"]').selectOption('NL');
        await page.locator('[data-test="postal_code"]').fill(faker.location.zipCode('####??'));
        await page.locator('[data-test="house_number"]').fill(houseNumber);
        await page.locator('[data-test="street"]').fill(street);
        await page.locator('[data-test="city"]').fill(faker.location.city());
        await page.locator('[data-test="state"]').fill(faker.location.state());
        await page.locator('[data-test="phone"]').fill(faker.string.numeric(10));
        await page.locator('[data-test="email"]').fill(email);
        await page.locator('[data-test="password"] input').fill(password);
        await page.locator('[data-test="register-submit"]').click();
        await expect(page).toHaveURL(/\/auth\/login/);

        await page.locator('[data-test="email"]').fill(email);
        await page.locator('[data-test="password"]').fill(password);
        await page.locator('[data-test="login-submit"]').click();
        await expect(page.locator('[data-test="page-title"]')).toContainText('My account');

        await page.goto('/');
        await page.locator('[data-test="search-query"]').fill('Slip Joint Pliers');
        await page.locator('[data-test="search-submit"]').click();
        await page.locator('[data-test="product-name"]').first().click();
        await page.locator('[data-test="add-to-cart"]').click();

        await page.locator('[data-test="nav-cart"]').click();
        await page.locator('[data-test="proceed-1"]').click();
        await page.locator('[data-test="proceed-2"]').click();

        await expect(page.locator('[data-test="street"]')).toHaveValue(street);
        await expect(page.locator('[data-test="house_number"]')).toHaveValue(houseNumber);
    });

});