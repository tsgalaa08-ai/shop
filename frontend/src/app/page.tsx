"use client";

import { useEffect, useState } from "react";

type Product = { id: number; name: string; slug: string; price: string; sale_price?: string | null; stock: number; image?: string | null; category?: { name: string } };
type Category = { id: number; name: string; slug: string; products_count: number };

const api = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api";
const money = (value: string | number) => `${Number(value).toLocaleString("mn-MN")}₮`;

export default function Home() {
  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [search, setSearch] = useState("");
  const [cartCount, setCartCount] = useState(0);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const query = search ? `?search=${encodeURIComponent(search)}` : "";
    Promise.all([fetch(`${api}/products${query}`).then((r) => r.json()), fetch(`${api}/categories`).then((r) => r.json())])
      .then(([productResponse, categoryResponse]) => { setProducts(productResponse.data?.data ?? []); setCategories(categoryResponse.data ?? []); })
      .catch(() => { setProducts([]); setCategories([]); })
      .finally(() => setLoading(false));
  }, [search]);

  const addToCart = (product: Product) => {
    if (product.stock > 0) setCartCount((count) => count + 1);
  };

  return (
    <main>
      <header className="site-header"><a className="brand" href="#top">ЖИЖИГ<span>SHOP</span></a><div className="search"><span>⌕</span><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Бүтээгдэхүүн хайх..." /></div><nav><a href="#categories">Ангилал</a><a href="#products">Бүтээгдэхүүн</a><a href="/checkout" className="cart">Сагс <b>{cartCount}</b></a></nav></header>
      <section className="hero" id="top"><div><p className="eyebrow">Өдөр бүрийн жижиг баяр</p><h1>Танд хэрэгтэй<br /><em>сайхан зүйлс.</em></h1><p className="hero-copy">Гэр, арьс арчилгаа, бэлгийн сонголтыг нэг дороос. Монголд хурдан хүргэнэ.</p><a className="primary" href="#products">Дэлгүүр үзэх <span>→</span></a></div><div className="hero-art"><div className="sun" /><div className="shape">MONGOLIA<br /><strong>LOCAL<br />GOODS</strong></div></div></section>
      <section className="section" id="categories"><div className="section-heading"><div><p className="eyebrow">Сонголтоо олоорой</p><h2>Ангилал</h2></div><span>Бүх бүтээгдэхүүн →</span></div><div className="categories">{categories.map((category) => <a className="category" href={`?category=${category.slug}`} key={category.id}><span>✦</span><strong>{category.name}</strong><small>{category.products_count} бүтээгдэхүүн</small></a>)}</div></section>
      <section className="section products-section" id="products"><div className="section-heading"><div><p className="eyebrow">Шинээр ирсэн</p><h2>Онцлох бүтээгдэхүүн</h2></div><span>{products.length} сонголт</span></div>{loading ? <p className="notice">Ачаалж байна...</p> : products.length === 0 ? <p className="notice">Бүтээгдэхүүн олдсонгүй.</p> : <div className="products">{products.map((product) => <article className="product" key={product.id}><div className="product-image"><span>{product.stock === 0 ? "Дууссан" : product.sale_price ? "Хямдрал" : "Шинэ"}</span>{product.image ? <div className="product-photo" role="img" aria-label={product.name} style={{ backgroundImage: `url(${product.image})` }} /> : <div>✦</div>}</div><p className="product-category">{product.category?.name}</p><h3>{product.name}</h3><div className="product-bottom"><div>{product.sale_price && <del>{money(product.price)}</del>}<strong>{money(product.sale_price ?? product.price)}</strong></div><button disabled={product.stock === 0} onClick={() => addToCart(product)} aria-label={`${product.name} сагсанд нэмэх`}>+</button></div></article>)}</div>}</section>
      <footer><div className="brand">ЖИЖИГ<span>SHOP</span></div><p>Жижиг дэлгүүрүүдийн сайхан сонголт.</p><p>© 2026 ЖИЖИГ SHOP</p></footer>
    </main>
  );
}
