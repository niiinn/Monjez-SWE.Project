let categories; 

try {
    let storedCategories = localStorage.getItem('categories');
    categories = storedCategories ? JSON.parse(storedCategories) : [];
} catch (error) {
    console.error("Error parsing categories from localStorage:", error);
    categories = []; 
}
        let selectedCategoryIndex = -1;

        function saveCategories() {
            localStorage.setItem('categories', JSON.stringify(categories));
        }

        function addCategory() {
            const categoryInput = document.getElementById('categoryInput');
            const categoryName = categoryInput.value.trim();
            if (!categoryName) {
                alert("Please enter a valid category name.");
                return;
            }
            categories.push({ name: categoryName, items: [] });
            categoryInput.value = "";
            saveCategories();
            renderCategories();
        }

        function renderCategories() {
            const container = document.getElementById('categoryContainer');
            container.innerHTML = '';
            categories.forEach((category, index) => {
                const categoryBox = document.createElement('div');
                categoryBox.classList.add('category-box');
                categoryBox.textContent = category.name;
                categoryBox.onclick = () => openCategory(index);
                container.appendChild(categoryBox);
            });
        }

function openCategory(index) {
    selectedCategoryIndex = index;
    document.getElementById('categoryTitle').innerText = categories[index].name;
    document.getElementById('categoryContainer').style.display = 'none';
    document.getElementById('categoryPanel').style.display = 'none';
    document.getElementById('itemContainer').style.display = 'block';
    document.getElementById('itemPanel').style.display = 'flex';
    
    document.querySelector(".back-button").classList.add("show");

    renderItems();
}

function goBack() {
    document.getElementById('categoryContainer').style.display = 'flex';
    document.getElementById('categoryPanel').style.display = 'flex';
    document.getElementById('itemContainer').style.display = 'none';
    document.getElementById('itemPanel').style.display = 'none';
      document.getElementById('categoryTitle').textContent = ''; 
    selectedCategoryIndex = -1;
    document.querySelector(".back-button").classList.remove("show"); 
}


        function addItem() {
            const itemInput = document.getElementById('itemInput');
            const itemQuantity = document.getElementById('itemQuantity');
            const itemName = itemInput.value.trim();
            const quantity = parseInt(itemQuantity.value);
            
            if (!itemName || isNaN(quantity) || quantity <= 0) {
                alert("Please enter a valid item name and quantity.");
                return;
            }
            
            categories[selectedCategoryIndex].items.push({ name: itemName, quantity: quantity });
            itemInput.value = "";
            itemQuantity.value = "";
            saveCategories();
            renderItems();
        }
function renderItems() {
    const itemList = document.getElementById('itemList');
    itemList.innerHTML = '';
    
    categories[selectedCategoryIndex].items.forEach((item, index) => {
        const itemBox = document.createElement('div');
        itemBox.classList.add('item-box');

        if (item.purchased) {
            itemBox.classList.add('purchased'); 
        }
        
        itemBox.innerHTML = `
            <input type="checkbox" class="item-checkbox" ${item.purchased ? "checked" : ""} onclick="MarkItemAsPurchased(${index})">
            <span>${item.name}</span>
            <input type="text" value="${item.name}" class="edit-name">
            <div class="quantity-container">
                <button onclick="changeQuantity(${index}, -1)">-</button>
                <span>${item.quantity}</span>
                <input type="number" value="${item.quantity}" class="edit-quantity">
                <button onclick="changeQuantity(${index}, 1)">+</button>
            </div>
            <div class="buttons-container">
                <button onclick="editItem(${index}, this)">✏️</button>
                <button onclick="confirmSave(${index}, this)" style="display:none;">💾</button>
                <button onclick="deleteItem(${index})">🗑️</button>
            </div>
        `;
        itemList.appendChild(itemBox);
    });
}


        function editItem(index, button) {
            const itemBox = button.parentElement.parentElement;
            itemBox.classList.add('edit-mode');
            itemBox.querySelector('.edit-name').style.display = 'inline-block';
            itemBox.querySelector('.edit-quantity').style.display = 'inline-block';
            button.style.display = 'none';
            itemBox.querySelector('button[onclick^="confirmSave"]').style.display = 'inline-block';
        }

        function confirmSave(index, button) {
            const itemBox = button.parentElement.parentElement;
            const newName = itemBox.querySelector('.edit-name').value;
            const newQuantity = itemBox.querySelector('.edit-quantity').value;
            
            categories[selectedCategoryIndex].items[index].name = newName;
            categories[selectedCategoryIndex].items[index].quantity = parseInt(newQuantity);
            saveCategories();
            renderItems();
        }
        function changeQuantity(index, change) {
            categories[selectedCategoryIndex].items[index].quantity += change;
            if (categories[selectedCategoryIndex].items[index].quantity < 1) {
                categories[selectedCategoryIndex].items.splice(index, 1);
            }
            saveCategories();
            renderItems();
        }
function deleteItem(index) {
    if (confirm("Are you sure you want to delete this item?")) {
        categories[selectedCategoryIndex].items.splice(index, 1);
        saveCategories();
        renderItems();
    }
}

function MarkItemAsPurchased(index) {
    categories[selectedCategoryIndex].items[index].purchased = !categories[selectedCategoryIndex].items[index].purchased;
    saveCategories();
    renderItems();
}


function loadUserName() {
    const userGreeting = document.getElementById("userGreeting");
    let username = localStorage.getItem("loggedInUser");

    if (username) {
        userGreeting.textContent = `Hello, ${username}`;
    } else {
        userGreeting.textContent = "Hello, Guest";
    }
}

function logout() {
    localStorage.removeItem("loggedInUser");
    document.getElementById("userGreeting").textContent = "Hello, Guest"; 
    window.location.href = "login.html";
}


document.addEventListener("DOMContentLoaded", () => {
    loadUserName();
    renderCategories(); 
});
