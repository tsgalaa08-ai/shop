export type Product = { id: number; name: string; slug: string; sku: string; price: string; sale_price?: string | null; stock: number; image?: string | null; description?: string | null; category?: { name: string; slug?: string } };
export type Category = { id: number; name: string; slug: string; products_count: number; image?: string | null };
export type CartItem = Product & { quantity: number };

export const api = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api";
export const money = (value: string | number) => `${Number(value).toLocaleString("mn-MN")}₮`;
export const priceOf = (product: Product) => Number(product.sale_price ?? product.price);

export function readCart(): CartItem[] { if (typeof window === "undefined") return []; try { return JSON.parse(localStorage.getItem("small-shop-cart") ?? "[]") as CartItem[]; } catch { return []; } }
export function writeCart(items: CartItem[]) { localStorage.setItem("small-shop-cart", JSON.stringify(items)); window.dispatchEvent(new Event("cart-change")); }
export function addToCart(product: Product, quantity = 1) { const cart = readCart(); const existing = cart.find((item) => item.id === product.id); if (existing) existing.quantity = Math.min(existing.quantity + quantity, product.stock); else cart.push({ ...product, quantity: Math.min(quantity, product.stock) }); writeCart(cart); }
export function cartTotal(items: CartItem[]) { return items.reduce((sum, item) => sum + priceOf(item) * item.quantity, 0); }