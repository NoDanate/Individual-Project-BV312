package com.example.audiobooks

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.*
import androidx.compose.material3.*
import androidx.compose.ui.Modifier
import androidx.navigation.compose.rememberNavController
import com.example.audiobooks.data.api.RetrofitClient
import com.example.audiobooks.ui.theme.AudioBooksTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        //Инициализация RetrofitClient
        RetrofitClient.init(this)

        setContent { // Тема приложения
            AudioBooksTheme() {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    // Навигация для переходов между экранами
                    val navController = rememberNavController()
                    AppNavigation(navController)
                }
            }
        }
    }
}