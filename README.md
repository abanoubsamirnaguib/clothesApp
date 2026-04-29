<p align="center">
  <a href="https://commerce.nuxt.dev" target="_blank" rel="noopener">
    <img alt="NuxtCommerce – Headless storefront for WooCommerce" src="https://github.com/user-attachments/assets/1c6720b7-5aea-4f6b-be55-8944fb81799a">
  </a>
</p>

<h1 align="center">NuxtCommerce</h1>

<p align="center">
  Modern, open-source headless storefront for WooCommerce — built with <strong>Nuxt 4</strong> & <strong>GraphQL</strong>.<br/>
  Pinterest-style UI, dark mode, multi-language, and a DX focused, production-ready setup.
  <br/><br/>
  <a href="https://commerce.nuxt.dev"><strong>🚀 Live Demo</strong></a>
  <br/><br/>
  <a href="https://github.com/zackha/nuxtcommerce/stargazers"><img alt="GitHub stars" src="https://img.shields.io/github/stars/zackha/nuxtcommerce?style=flat&color=ff4757"></a>
  <a href="https://github.com/zackha/nuxtcommerce/issues"><img alt="Issues" src="https://img.shields.io/github/issues/zackha/nuxtcommerce?style=flat"></a>
  <img alt="Built with Nuxt" src="https://img.shields.io/badge/Nuxt-238636?logo=nuxt&logoColor=white&style=flat"/>
  <img alt="GraphQL" src="https://img.shields.io/badge/GraphQL-E10098?logo=graphql&logoColor=white&style=flat"/>
  <img alt="WooCommerce" src="https://img.shields.io/badge/WooCommerce-96588A?logo=woo&logoColor=white&style=flat"/>
</p>

## Table of Contents

* [Features](#features)
* [Tech Stack](#tech-stack)
* [Quickstart](#quickstart)
* [Configuration](#configuration)
* [WordPress + WooCommerce + WPGraphQL Setup](#wordpress--woocommerce--wpgraphql-setup)
* [Architecture Overview](#architecture-overview)
* [API Endpoints (Server)](#api-endpoints-server)
* [Internationalization (i18n)](#internationalization-i18n)
* [Performance & Caching](#performance--caching)
* [Contributors & Acknowledgements](#-contributors--acknowledgements)
* [Contact](#contact)

## Features

* 🎯 **Headless WooCommerce** store powered by **WPGraphQL** (+ WooGraphQL)
* ⚡️ **Nuxt 4 + Nitro** with server-side GraphQL proxy, **SWR** caching & route rules
* 🧭 **Pinterest-style product grid** with **infinite scroll**
* 🛒 **Cart & Checkout** (WooCommerce session cookie handled server-side)
* ❤️ **Wishlist** (localStorage) & **Favorites** page
* 🌙 **Dark mode** + sleek micro-interactions (skeletons, transitions)
* 🌐 **Multi-language** (en, nb, nl, de) via `@nuxtjs/i18n`
* 🖼️ Optimized images with `@nuxt/image`
* 🔔 Friendly toasts with **Notivue**
* 🔎 Good **SEO defaults** + **JSON-LD Product schema**
* ☁️ Ready for **NuxtHub** / Cloudflare Workers (KV cache)

## Tech Stack

* **Framework:** Nuxt 4, Vue 3
* **GraphQL Client (server):** `graphql-request` (`GraphQLClient` + `gql`)
* **Styling/UI:** Tailwind CSS, `@nuxt/ui`, Icons (Iconify)
* **Images:** `@nuxt/image`
* **i18n:** `@nuxtjs/i18n`
* **Toasts:** `notivue`
* **Deployment (optional):** NuxtHub + Cloudflare Workers

> Project scripts live in `package.json` (`dev`, `dev:ssl`, `build`, `generate`, `preview`, `deploy`).

## Quickstart

### Requirements

* **Node.js** `>= 18.20` (or 20.x)
* **pnpm** (project uses `pnpm@10.x`)
* A WordPress backend with WooCommerce + WPGraphQL (see setup below)

### 1) Clone & install

```bash
git clone https://github.com/zackha/nuxtcommerce.git
cd nuxtcommerce
pnpm install
```

### 2) Configure environment

Create `.env`:

```bash
GQL_HOST=https://your-woocommerce-site.com/graphql
```

### 3) Run

```bash
pnpm run dev
# optional HTTPS local dev
pnpm run dev:ssl
```

App runs at **[http://localhost:3000](http://localhost:3000)**

### 4) Build / Preview

```bash
pnpm run build
pnpm run preview
```

## Configuration

Key settings are in **`nuxt.config.ts`**:

* **Runtime Config:** `runtimeConfig.gqlHost` (reads `GQL_HOST`)
* **Modules:** `@nuxt/ui`, `@nuxt/image`, `@nuxtjs/i18n`, `notivue/nuxt`, `@nuxthub/core`
* **Route Rules:** SWR caching for `/categories` and `/favorites`, prerender for `/`
* **Nitro Prerender:** `/sitemap.xml`, `/robots.txt`
* **NuxtHub Cache:** `hub: { cache: true }` (optional KV cache)

## WordPress + WooCommerce + WPGraphQL Setup

> This project uses **WordPress + WooCommerce** as the headless backend and **WPGraphQL** (+ WooGraphQL) as the API layer consumed by the Nuxt app. Follow the steps below carefully.

### 1) Fresh WordPress install (quick checklist)

1. Install WordPress on your host (or local with e.g. Local, MAMP, Docker).
2. Log in to `/wp-admin`.
3. Go to **Settings → General** and set:

   * Site Language, Timezone, Date/Time format
   * Ensure **WordPress Address** and **Site Address** use **https\://** in production
4. Go to **Settings → Permalinks** and choose **Post name**.

   > Pretty permalinks are required for `/graphql`.

### 2) Required plugins

Install and activate:

* [**WooCommerce**](https://woocommerce.com/) – core e-commerce
* [**WPGraphQL**](https://www.wpgraphql.com/) – GraphQL API for WordPress
* [**WPGraphQL WooCommerce (WooGraphQL)**](https://woographql.com/) – WooCommerce schema for WPGraphQL
* *(Optional)* Regenerate Thumbnails – rebuild image sizes after changes

### 3) WooCommerce onboarding

* **General:** Store address, selling/shipping regions, currency (NOK/EUR/USD…)
* **Products:** Reviews on/off, measurements
* **Tax:** Enable and define rates (if applicable)
* **Shipping:** Create at least one zone + method (e.g., Flat rate)
* **Payments:** Enable **Cash on Delivery** (COD) for quick E2E testing
* **Accounts & Privacy:** Decide guest checkout
* **Advanced:** REST is not required; GraphQL is separate

> Demo checkout posts `paymentMethod: 'cod'` — ensure COD is enabled for testing.

### 4) Product attributes & taxonomies (used in GraphQL)

Create attributes in **Products → Attributes**:

* **Color** (`slug: color` → taxonomy `pa_color`) → used via `allPaColor`
* **Style** (`slug: style` → taxonomy `pa_style`) → used via `allPaStyle`

Add terms (e.g., Color: Red/Blue/Black; Style: Casual/Sport).

### 5) Import products (CSV) or create manually

**CSV Import (recommended for demo)**

1. Download [`public/products.zip`](https://github.com/zackha/nuxtcommerce/raw/refs/heads/master/public/products.zip) from the repo.
2. **Products → Import**, upload CSV(s), map columns:

   * **variable** for parent products
   * Attributes → `pa_color`, `pa_style`.
   * Variations CSV must reference correct parent
3. Ensure products are **Published**, **In Stock**, with prices.

**Manual**

* Variable product → add attributes (used for variations) → create variations from attributes → set price/stock → set images.

### 6) Media sizes (thumbnails & large)

Frontend queries use:

* `WOOCOMMERCE_THUMBNAIL`
* `LARGE`

Check sizes in **WooCommerce** (thumbnails) & **Settings → Media** (large).
If you tweak sizes or bulk import images, run **Regenerate Thumbnails**.

### 7) Connect frontend → backend

Create `.env` in the Nuxt project:

```bash
GQL_HOST=https://your-woocommerce-site.com/graphql
```

This is read by `runtimeConfig.gqlHost` and used by the server utility that proxies & caches GraphQL calls.

## Architecture Overview

```
/app
  ├─ app.vue                      # Global head/meta + header/footer + Notivue
  ├─ app.config.ts                # Site name/description, UI theme
  ├─ pages/
  │   ├─ index.vue                # Product grid, infinite scroll, filters
  │   ├─ categories.vue           # Category grid
  │   ├─ favorites.vue            # Wishlist page
  │   └─ product/[id].vue         # Product detail, gallery, variations, schema.org
  ├─ components/                  # UI building blocks (cards, carousels, cart, checkout...)
  ├─ composables/                 # useCart, useCheckout, useWishlist, useComponents
  └─ gql/                         # GraphQL queries & mutations

/server
  ├─ api/
  │   ├─ products.get.ts          # GET products (cursor pagination) — cached (SWR)
  │   ├─ product.get.ts           # GET product detail — cached (SWR)
  │   ├─ search.get.ts            # GET search (top 6) — cached (SWR)
  │   ├─ categories.get.ts        # GET categories — cached (SWR)
  │   ├─ cart/add.post.ts         # POST add to cart (Woo session cookie handling)
  │   ├─ cart/update.post.ts      # POST update quantities / remove
  │   └─ checkout.post.ts         # POST checkout (COD demo)
  ├─ routes/
  │   ├─ sitemap.xml.ts           # Minimal sitemap
  │   └─ robots.txt.ts            # Robots
  └─ utils/wpgraphql.ts           # GraphQL client + error wrapper + Woo session cookie
```

**Flow:**
Client (`$fetch` to `/api/*`) → Nitro server proxies to WPGraphQL → GET endpoints are cached (SWR); POST endpoints manage the WooCommerce session cookie.

## API Endpoints (Server)

* `GET /api/products?search=&category=&orderby=DESC|ASC&fieldby=DATE|PRICE&after=...`
* `GET /api/products/:slug`
* `GET /api/products?search=:q` (use first 6 for quick search)
* `GET /api/categories`
* `POST /api/cart/add` `{ productId }`
* `POST /api/cart/update` `{ items: [{ key, quantity }] }`
* `POST /api/checkout` `{ billing: {...}, paymentMethod: 'cod' }`

## Internationalization (i18n)

* Locales: **en-GB**, **nb-NO**, **nl-NL**, **de-DE**
* Default: **en**
* Use `useLocalePath()` for links; SEO tags adapt per route.

## Performance & Caching

* **`cachedEventHandler`** on GET handlers with **SWR** (stale-while-revalidate)
* **Route Rules** for `/categories` and `/favorites`
* Optional **NuxtHub KV cache** (`hub: { cache: true }`)
* Image optimization via `@nuxt/image`
* Prerender: `/`, `/sitemap.xml`, `/robots.txt`

## 🙌 Contributors & Acknowledgements

We sincerely thank everyone who has contributed to **NuxtCommerce**.
Your support, feedback, and ideas keep this project moving forward. 🚀

✨ **Special thanks**

|                                                    Collaborator                                                    |
| :----------------------------------------------------------------------------------------------------------------: |
| [<img src="https://github.com/rikp777.png?size=115" width=115><br><sub>@rikp777</sub>](https://github.com/rikp777) |

<sub>More contributors will be highlighted here as the project grows.</sub>

## Contact

Have questions or suggestions?

* Email: **[zckhtln@icloud.com](mailto:zckhtln@icloud.com)**
* X (Twitter): [**@ZHatlen**](https://twitter.com/ZHatlen)


> **Note**  
> You can view the orders you create during the live demo at [NuxtCommerce Admin](https://nuxtcommerce-admin.vercel.app/).  
> From there, you can also update their statuses and add notes to your orders.
