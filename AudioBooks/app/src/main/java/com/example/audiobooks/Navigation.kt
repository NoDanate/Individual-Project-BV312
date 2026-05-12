package com.example.audiobooks

import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Favorite
import androidx.compose.material.icons.filled.List
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import com.example.audiobooks.ui.screens.bookdetail.BookDetailScreen
import com.example.audiobooks.ui.screens.catalog.CatalogScreen
import com.example.audiobooks.ui.screens.login.LoginScreen
import com.example.audiobooks.ui.screens.wishlist.WishlistScreen
import com.example.audiobooks.utils.SessionManager

//Определяет логин, каталог и избранное, детали книги
@Composable
fun AppNavigation(rootNavController: NavHostController) {
    val context = LocalContext.current
    val sessionManager = remember { SessionManager(context) }
    val isLoggedIn = sessionManager.isLoggedIn()
    //Проверка на авторизованность
    val startDestination = if (isLoggedIn) "main" else "login"

    NavHost(
        navController = rootNavController,
        startDestination = startDestination
    ) {
        // Экран входа/регистрации
        composable("login") {
            LoginScreen(
                onLoginSuccess = {
                    rootNavController.navigate("main") {
                        popUpTo(0) { inclusive = true }
                    }
                }
            )
        }
        // Главное окно с нижней панелью для навигации (каталог и избранное)
        composable("main") {
            MainScreen(
                onBookClick = { bookId -> rootNavController.navigate("book/$bookId") },
                onLogout = {
                    sessionManager.logout()
                    rootNavController.navigate("login") {
                        popUpTo(0) { inclusive = true }
                    }
                }
            )
        }
        // Детали книги (карточка)
        composable("book/{bookId}") { backStackEntry ->
            val bookId = backStackEntry.arguments?.getString("bookId")?.toIntOrNull() ?: 0
            BookDetailScreen(
                bookId = bookId,
                onBack = { rootNavController.popBackStack() },
                onBookClick = { newBookId ->
                    rootNavController.navigate("book/$newBookId")
                }
            )
        }
    }
}
//Нижняя панель навигации и основной экран
@Composable
fun MainScreen(
    onBookClick: (Int) -> Unit,
    onLogout: () -> Unit
) {
    var selectedTab by remember { mutableIntStateOf(0) }

    Scaffold(
        bottomBar = {
            NavigationBar {
                NavigationBarItem(
                    icon = { Icon(Icons.Default.List, contentDescription = "Каталог") },
                    label = { Text("Каталог") },
                    selected = selectedTab == 0,
                    onClick = { selectedTab = 0 }
                )
                NavigationBarItem(
                    icon = { Icon(Icons.Default.Favorite, contentDescription = "Избранное") },
                    label = { Text("Избранное") },
                    selected = selectedTab == 1,
                    onClick = { selectedTab = 1 }
                )
            }
        }
    ) { innerPadding ->
        Box(modifier = Modifier.padding(innerPadding)) {
            when (selectedTab) {
                0 -> CatalogScreen(
                    onBookClick = onBookClick,
                    onLogout = onLogout
                )
                1 -> WishlistScreen(
                    onBookClick = onBookClick
                )
            }
        }
    }
}