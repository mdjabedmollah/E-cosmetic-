document.addEventListener("DOMContentLoaded", () => {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
    const mainNav = document.querySelector(".main-nav")
  
    if (mobileMenuToggle) {
      mobileMenuToggle.addEventListener("click", function () {
        mainNav.style.display = mainNav.style.display === "block" ? "none" : "block"
        this.classList.toggle("active")
      })
    }
  
    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll(".add-to-cart")
    const addToCartDetailButton = document.querySelector(".add-to-cart-detail")
  
    if (addToCartButtons.length > 0) {
      addToCartButtons.forEach((button) => {
        button.addEventListener("click", function () {
          const productId = this.getAttribute("data-id")
          addToCart(productId, 1)
        })
      })
    }
  
    if (addToCartDetailButton) {
      addToCartDetailButton.addEventListener("click", function () {
        const productId = this.getAttribute("data-id")
        const quantity = document.getElementById("quantity").value
        addToCart(productId, quantity)
      })
    }
  
    // Cart quantity controls
    const increaseButtons = document.querySelectorAll(".quantity-btn.increase")
    const decreaseButtons = document.querySelectorAll(".quantity-btn.decrease")
    const removeButtons = document.querySelectorAll(".remove-item")
  
    if (increaseButtons.length > 0) {
      increaseButtons.forEach((button) => {
        button.addEventListener("click", function () {
          const productId = this.getAttribute("data-id")
          const quantityElement = this.parentElement.querySelector(".quantity")
          const currentQuantity = Number.parseInt(quantityElement.textContent)
          updateCartQuantity(productId, currentQuantity + 1)
        })
      })
    }
  
    if (decreaseButtons.length > 0) {
      decreaseButtons.forEach((button) => {
        button.addEventListener("click", function () {
          const productId = this.getAttribute("data-id")
          const quantityElement = this.parentElement.querySelector(".quantity")
          const currentQuantity = Number.parseInt(quantityElement.textContent)
          if (currentQuantity > 1) {
            updateCartQuantity(productId, currentQuantity - 1)
          }
        })
      })
    }
  
    if (removeButtons.length > 0) {
      removeButtons.forEach((button) => {
        button.addEventListener("click", function () {
          const productId = this.getAttribute("data-id")
          removeFromCart(productId)
        })
      })
    }
  
    // Cart functions
    function addToCart(productId, quantity) {
      const formData = new FormData()
      formData.append("action", "add")
      formData.append("product_id", productId)
      formData.append("quantity", quantity)
  
      fetch("api/cart.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            updateCartCount(data.cart_count)
            alert("Product added to cart!")
          } else {
            alert(data.message || "Error adding product to cart")
          }
        })
        .catch((error) => {
          console.error("Error:", error)
        })
    }
  
    function updateCartQuantity(productId, quantity) {
      const formData = new FormData()
      formData.append("action", "update")
      formData.append("product_id", productId)
      formData.append("quantity", quantity)
  
      fetch("api/cart.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            updateCartCount(data.cart_count)
            // Reload the page to update the cart display
            location.reload()
          } else {
            alert(data.message || "Error updating cart")
          }
        })
        .catch((error) => {
          console.error("Error:", error)
        })
    }
  
    function removeFromCart(productId) {
      const formData = new FormData()
      formData.append("action", "remove")
      formData.append("product_id", productId)
  
      fetch("api/cart.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            updateCartCount(data.cart_count)
            // Reload the page to update the cart display
            location.reload()
          } else {
            alert(data.message || "Error removing product from cart")
          }
        })
        .catch((error) => {
          console.error("Error:", error)
        })
    }
  
    function updateCartCount(count) {
      const cartCountElement = document.querySelector(".cart-count")
      if (cartCountElement) {
        if (count > 0) {
          cartCountElement.textContent = count
          cartCountElement.style.display = "flex"
        } else {
          cartCountElement.style.display = "none"
        }
      }
    }
  })
  
  // User dropdown menu
  const userMenu = document.querySelector(".user-menu")
  if (userMenu) {
    const userDropdown = userMenu.querySelector(".user-dropdown")
  
    // Show dropdown on hover for desktop
    userMenu.addEventListener("mouseenter", () => {
      if (window.innerWidth >= 768) {
        userDropdown.style.display = "block"
      }
    })
  
    userMenu.addEventListener("mouseleave", () => {
      if (window.innerWidth >= 768) {
        userDropdown.style.display = "none"
      }
    })
  
    // Toggle dropdown on click for mobile
    userMenu.querySelector(".account-icon").addEventListener("click", (e) => {
      if (window.innerWidth < 768) {
        e.preventDefault()
        userDropdown.style.display = userDropdown.style.display === "block" ? "none" : "block"
      }
    })
  }
  