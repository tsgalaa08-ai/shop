"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { addToCart, api, Category, money, Product } from "../lib/shop";

export default function Home() {
  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [search, setSearch] = useState("");
  const [cartCount, setCartCount] = useState(0);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const category = typeof window !== "undefined" ? new URLSearchParams(window.location.search).get("category") : "";
    const params = new URLSearchParams(); if (search) params.set("search", search); if (category) params.set("category", category);
    const query = params.toString() ? `?${params.toString()}` : "";
    Promise.all([fetch(`${api}/products${query}`).then((r) => r.json()), fetch(`${api}/categories`).then((r) => r.json())])
      .then(([productResponse, categoryResponse]) => { setProducts(productResponse.data?.data ?? []); setCategories(categoryResponse.data ?? []); })
      .catch(() => { setProducts([]); setCategories([]); })
      .finally(() => setLoading(false));
  }, [search]);

  useEffect(() => { const update = () => setCartCount(JSON.parse(localStorage.getItem("small-shop-cart") ?? "[]").reduce((sum: number, item: { quantity: number }) => sum + item.quantity, 0)); update(); window.addEventListener("cart-change", update); return () => window.removeEventListener("cart-change", update); }, []);

  return (
    <main>
      <header className="site-header"><Link className="brand" href="#top">ЖИЖИГ<span>SHOP</span></Link><div className="search"><span>⌕</span><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Бүтээгдэхүүн, SKU хайх..." /></div><nav><a href="#categories">Ангилал</a><a href="#products">Бүтээгдэхүүн</a><Link href="/checkout" className="cart">Сагс <b>{cartCount}</b></Link></nav></header>
      <section className="hero" id="top"><div><p className="eyebrow">Өдөр бүрийн жижиг баяр</p><h1>Танд хэрэгтэй<br /><em>сайхан зүйлс.</em></h1><p className="hero-copy">Гэр, арьс арчилгаа, бэлгийн сонголтыг нэг дороос. Монголд хурдан хүргэнэ.</p><a className="primary" href="#products">Дэлгүүр үзэх <span>→</span></a></div><div className="hero-art"><div className="sun" /><div className="shape">MONGOLIA<br /><strong>LOCAL<br />GOODS</strong></div></div></section>
      <section className="section" id="categories"><div className="section-heading"><div><p className="eyebrow">Сонголтоо олоорой</p><h2>Ангилал</h2></div><span>{categories.length} ангилал</span></div><div className="categories">{categories.map((category) => <Link className="category" href={`/?category=${category.slug}#products`} key={category.id}><span>✦</span><strong>{category.name}</strong><small>{category.products_count} бүтээгдэхүүн</small></Link>)}</div></section>
      <section className="section products-section" id="products"><div className="section-heading"><div><p className="eyebrow">Шинээр ирсэн</p><h2>Онцлох бүтээгдэхүүн</h2></div><span>{products.length} сонголт</span></div>{loading ? <p className="notice">Ачаалж байна...</p> : products.length === 0 ? <p className="notice">Бүтээгдэхүүн олдсонгүй.</p> : <div className="products">{products.map((product) => <article className="product" key={product.id}><Link href={`/products/${product.slug}`}><div className="product-image"><span>{product.stock === 0 ? "Дууссан" : product.sale_price ? "Хямдрал" : "Шинэ"}</span>{product.image ? <div className="product-photo" role="img" aria-label={product.name} style={{ backgroundImage: `url(${product.image})` }} /> : <div>✦</div>}</div><p className="product-category">{product.category?.name}</p><h3>{product.name}</h3></Link><div className="product-bottom"><div>{product.sale_price && <del>{money(product.price)}</del>}<strong>{money(product.sale_price ?? product.price)}</strong></div><button disabled={product.stock === 0} onClick={() => addToCart(product)} aria-label={`${product.name} сагсанд нэмэх`}>+</button></div></article>)}</div>}</section>
      <footer><div className="brand">ЖИЖИГ<span>SHOP</span></div><p>Жижиг дэлгүүрүүдийн сайхан сонголт.</p><p>© 2026 ЖИЖИГ SHOP</p></footer>
    </main>
  );
}
