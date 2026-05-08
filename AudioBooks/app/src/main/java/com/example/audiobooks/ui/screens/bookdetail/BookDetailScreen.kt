package com.example.audiobooks.ui.screens.bookdetail

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import coil.compose.AsyncImage
import com.example.audiobooks.data.models.SimilarBook
import com.example.audiobooks.ui.components.AudioPlayerBar
import com.example.audiobooks.ui.screens.player.AudioPlayerViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun BookDetailScreen(
    bookId: Int,
    onBack: () -> Unit,
    onBookClick: (Int) -> Unit,
    viewModel: BookDetailViewModel = viewModel(),
    audioPlayerViewModel: AudioPlayerViewModel = viewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    val playerState by audioPlayerViewModel.uiState.collectAsState()

    LaunchedEffect(bookId) {
        viewModel.loadBookDetail(bookId)
    }

    Box(modifier = Modifier.fillMaxSize()) {
        Scaffold(
            topBar = {
                TopAppBar(
                    title = { Text(uiState.bookDetail?.name ?: "Загрузка...") },
                    navigationIcon = {
                        IconButton(onClick = onBack) {
                            Icon(Icons.Default.ArrowBack, contentDescription = "Назад")
                        }
                    }
                )
            }
        ) { paddingValues ->
            if (uiState.isLoading) {
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(paddingValues),
                    contentAlignment = Alignment.Center
                ) {
                    CircularProgressIndicator()
                }
            } else if (uiState.error != null) {
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(paddingValues),
                    contentAlignment = Alignment.Center
                ) {
                    Text(uiState.error ?: "Ошибка", color = MaterialTheme.colorScheme.error)
                }
            } else if (uiState.bookDetail != null) {
                val book = uiState.bookDetail!!
                val selectedSpeaker = uiState.selectedSpeaker

                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(paddingValues)
                        .verticalScroll(rememberScrollState())
                        .padding(bottom = if (playerState.isPlayerVisible) 140.dp else 0.dp)
                ) {
                    // Изображение
                    AsyncImage(
                        model = book.imageUrl,
                        contentDescription = book.name,
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(400.dp),
                        contentScale = ContentScale.Fit
                    )

                    // Информация
                    Column(modifier = Modifier.padding(16.dp)) {
                        // Название и автор
                        Text(
                            text = book.name,
                            style = MaterialTheme.typography.headlineSmall,
                            fontWeight = FontWeight.Bold
                        )

                        Spacer(modifier = Modifier.height(4.dp))

                        Text(
                            text = book.author,
                            style = MaterialTheme.typography.titleMedium,
                            color = MaterialTheme.colorScheme.primary
                        )

                        Spacer(modifier = Modifier.height(8.dp))

                        // Жанр и рейтинг
                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween,
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            AssistChip(
                                onClick = {},
                                label = { Text(book.genre) }
                            )

                            Row(verticalAlignment = Alignment.CenterVertically) {
                                StarRating(
                                    rating = book.rating,
                                    maxStars = 5,
                                    size = 20
                                )
                                Spacer(modifier = Modifier.width(4.dp))
                                Text(
                                    text = "(${book.ratingCount})",
                                    style = MaterialTheme.typography.bodySmall
                                )
                            }
                        }

                        Spacer(modifier = Modifier.height(16.dp))

                        // Выбор рассказчика
                        if (book.speakers.size > 1) {
                            Text(
                                text = "Рассказчики:",
                                style = MaterialTheme.typography.titleSmall,
                                fontWeight = FontWeight.Bold
                            )

                            Spacer(modifier = Modifier.height(8.dp))

                            Row(
                                horizontalArrangement = Arrangement.spacedBy(8.dp)
                            ) {
                                book.speakers.forEach { speaker ->
                                    FilterChip(
                                        selected = selectedSpeaker?.id == speaker.id,
                                        onClick = { viewModel.onSpeakerSelected(speaker.id) },
                                        label = { Text(speaker.name) }
                                    )
                                }
                            }

                            Spacer(modifier = Modifier.height(8.dp))
                        }

                        // Текущий рассказчик
                        Text(
                            text = "Рассказчик: ${selectedSpeaker?.name ?: book.speaker}",
                            style = MaterialTheme.typography.bodyMedium,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )

                        Spacer(modifier = Modifier.height(16.dp))

                        // Кнопка прослушивания
                        Button(
                            onClick = {
                                val audioUrl = selectedSpeaker?.audioUrl ?: book.audioUrl
                                audioPlayerViewModel.loadAudio(
                                    url = audioUrl,
                                    bookName = book.name,
                                    speaker = selectedSpeaker?.name ?: book.speaker,
                                    imageUrl = book.imageUrl
                                )
                            },
                            modifier = Modifier.fillMaxWidth(),
                            colors = ButtonDefaults.buttonColors(
                                containerColor = MaterialTheme.colorScheme.primary
                            )
                        ) {
                            Icon(
                                Icons.Default.PlayArrow,
                                contentDescription = null,
                                modifier = Modifier.size(24.dp)
                            )
                            Spacer(modifier = Modifier.width(8.dp))
                            Text(
                                text = "Слушать",
                                style = MaterialTheme.typography.titleMedium
                            )
                        }

                        Spacer(modifier = Modifier.height(12.dp))

                        // Оценка пользователя
                        Card(
                            modifier = Modifier.fillMaxWidth()
                        ) {
                            Column(modifier = Modifier.padding(16.dp)) {
                                Text(
                                    text = if (book.userRating != null) "Ваша оценка" else "Оцените книгу",
                                    style = MaterialTheme.typography.titleSmall
                                )

                                Spacer(modifier = Modifier.height(8.dp))

                                Row(
                                    horizontalArrangement = Arrangement.Center,
                                    modifier = Modifier.fillMaxWidth()
                                ) {
                                    for (i in 1..5) {
                                        IconButton(onClick = { viewModel.rateBook(i) }) {
                                            Icon(
                                                imageVector = if (i <= (book.userRating ?: 0)) {
                                                    Icons.Default.Star
                                                } else {
                                                    Icons.Default.StarOutline
                                                },
                                                contentDescription = "Оценка $i",
                                                tint = if (i <= (book.userRating ?: 0)) {
                                                    MaterialTheme.colorScheme.primary
                                                } else {
                                                    MaterialTheme.colorScheme.outline
                                                },
                                                modifier = Modifier.size(36.dp)
                                            )
                                        }
                                    }
                                }

                                if (book.userRating != null) {
                                    Spacer(modifier = Modifier.height(4.dp))
                                    Text(
                                        text = "Вы поставили ${book.userRating} из 5",
                                        style = MaterialTheme.typography.bodySmall,
                                        color = MaterialTheme.colorScheme.onSurfaceVariant
                                    )
                                }
                            }
                        }

                        Spacer(modifier = Modifier.height(12.dp))

                        // Кнопка избранного
                        Button(
                            onClick = { viewModel.toggleWishlist() },
                            modifier = Modifier.fillMaxWidth(),
                            colors = ButtonDefaults.buttonColors(
                                containerColor = if (book.inWishlist) {
                                    MaterialTheme.colorScheme.error
                                } else {
                                    MaterialTheme.colorScheme.secondary
                                }
                            )
                        ) {
                            Icon(
                                imageVector = if (book.inWishlist) {
                                    Icons.Default.Favorite
                                } else {
                                    Icons.Default.FavoriteBorder
                                },
                                contentDescription = null
                            )
                            Spacer(modifier = Modifier.width(8.dp))
                            Text(
                                text = if (book.inWishlist) "Удалить из избранного" else "В избранное"
                            )
                        }

                        Spacer(modifier = Modifier.height(16.dp))

                        // Описание
                        if (!book.description.isNullOrEmpty()) {
                            Text(
                                text = "Описание",
                                style = MaterialTheme.typography.titleSmall,
                                fontWeight = FontWeight.Bold
                            )
                            Spacer(modifier = Modifier.height(4.dp))
                            Text(
                                text = book.description,
                                style = MaterialTheme.typography.bodyMedium
                            )
                        }

                        Spacer(modifier = Modifier.height(16.dp))

                        // Похожие книги
                        if (book.similarBooks.isNotEmpty()) {
                            Text(
                                text = "Похожие книги",
                                style = MaterialTheme.typography.titleSmall,
                                fontWeight = FontWeight.Bold
                            )

                            Spacer(modifier = Modifier.height(8.dp))

                            Row(
                                horizontalArrangement = Arrangement.spacedBy(8.dp)
                            ) {
                                book.similarBooks.take(4).forEach { similar ->
                                    SimilarBookCard(
                                        book = similar,
                                        onClick = { onBookClick(similar.id) }
                                    )
                                }
                            }
                        }
                    }
                }
            }
        }

        // Аудиоплеер внизу экрана
        AudioPlayerBar(
            uiState = playerState,
            onPlayPause = { audioPlayerViewModel.playPause() },
            onSeek = { audioPlayerViewModel.seekTo(it) },
            onSkipForward = { audioPlayerViewModel.skipForward() },
            onSkipBackward = { audioPlayerViewModel.skipBackward() },
            onSpeedChange = { audioPlayerViewModel.setSpeed(it) },
            onClose = { audioPlayerViewModel.hidePlayer() },
            modifier = Modifier.align(Alignment.BottomCenter)
        )
    }
}

@Composable
fun SimilarBookCard(
    book: SimilarBook,
    onClick: () -> Unit
) {
    Card(
        modifier = Modifier
            .width(120.dp)
            .clickable { onClick() },
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(8.dp)) {
            AsyncImage(
                model = book.imageUrl,
                contentDescription = book.name,
                modifier = Modifier
                    .fillMaxWidth()
                    .height(120.dp),
                contentScale = ContentScale.Crop
            )

            Spacer(modifier = Modifier.height(4.dp))

            Text(
                text = book.name,
                style = MaterialTheme.typography.bodySmall,
                maxLines = 2
            )

            Spacer(modifier = Modifier.height(2.dp))

            Text(
                text = book.author,
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                maxLines = 1
            )
        }
    }
}

@Composable
fun StarRating(
    rating: Double,
    maxStars: Int = 5,
    size: Int = 20
) {
    Row {
        for (i in 1..maxStars) {
            Icon(
                imageVector = when {
                    i <= rating.toInt() -> Icons.Default.Star
                    i - 0.5 <= rating -> Icons.Default.StarHalf
                    else -> Icons.Default.StarOutline
                },
                contentDescription = null,
                modifier = Modifier.size(size.dp),
                tint = if (i <= rating) {
                    MaterialTheme.colorScheme.primary
                } else {
                    MaterialTheme.colorScheme.outline
                }
            )
        }
    }
}