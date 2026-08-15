let cart = JSON.parse(localStorage.getItem("canteenCart")) || [];

function addToCart(id, name, price, image) {

    const existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: id,
            name: name,
            price: price,
            image: image,
            quantity: 1
        });
    }

    localStorage.setItem("canteenCart", JSON.stringify(cart));

    updateCartCount();

    alert(name + " added to cart!");
}

function updateCartCount() {

    const countElement = document.getElementById("cart-count");

    if (!countElement) return;

    let total = 0;

    cart.forEach(item => {
        total += item.quantity;
    });

    countElement.textContent = total;
}

function removeFromCart(id) {

    cart = cart.filter(item => item.id !== id);

    localStorage.setItem("canteenCart", JSON.stringify(cart));

    location.reload();
}

function increaseQuantity(id) {

    const item = cart.find(item => item.id === id);

    if (item) {
        item.quantity++;
    }

    localStorage.setItem("canteenCart", JSON.stringify(cart));

    location.reload();
}

function decreaseQuantity(id) {

    const item = cart.find(item => item.id === id);

    if (!item) return;

    if (item.quantity > 1) {
        item.quantity--;
    } else {
        cart = cart.filter(item => item.id !== id);
    }

    localStorage.setItem("canteenCart", JSON.stringify(cart));

    location.reload();
}

function clearCart() {

    localStorage.removeItem("canteenCart");

    cart = [];

    location.reload();
}

document.addEventListener("DOMContentLoaded", function () {
    updateCartCount();
});