package com.example.audiobooks.ui.screens.player

import android.app.Application
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import androidx.media3.common.MediaItem
import androidx.media3.common.Player
import androidx.media3.exoplayer.ExoPlayer
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.isActive
import kotlinx.coroutines.launch
// Воспроизведение, перемотка, скорость и состояние плеера
class AudioPlayerViewModel(application: Application) : AndroidViewModel(application) {

    private val _uiState = MutableStateFlow(AudioPlayerUiState())
    val uiState: StateFlow<AudioPlayerUiState> = _uiState.asStateFlow()

    private var player: ExoPlayer? = null
    // Инициализация плеера при первом использовании
    fun initPlayer() {
        if (player != null) return

        player = ExoPlayer.Builder(getApplication())
            .setHandleAudioBecomingNoisy(true)
            .build()
            .also { exoPlayer ->
                exoPlayer.addListener(object : Player.Listener {
                    override fun onPlaybackStateChanged(state: Int) {
                        _uiState.value = _uiState.value.copy(
                            isPlaying = state == Player.STATE_READY && exoPlayer.playWhenReady,
                            isBuffering = state == Player.STATE_BUFFERING
                        )
                    }

                    override fun onIsPlayingChanged(isPlaying: Boolean) {
                        _uiState.value = _uiState.value.copy(isPlaying = isPlaying)
                    }
                })
            }

        viewModelScope.launch {
            while (isActive) {
                player?.let {
                    _uiState.value = _uiState.value.copy(
                        currentPosition = it.currentPosition,
                        duration = if (it.duration > 0) it.duration else 0L
                    )
                }
                delay(500)
            }
        }
    }
    // Загрузка файла и начало воспроизведения
    fun loadAudio(url: String, bookName: String = "", speaker: String = "", imageUrl: String = "") {
        initPlayer()

        val mediaItem = MediaItem.fromUri(url)
        player?.apply {
            stop()
            setMediaItem(mediaItem)
            prepare()
            play()
        }

        _uiState.value = _uiState.value.copy(
            isPlayerVisible = true,
            currentAudioUrl = url,
            bookName = bookName,
            speaker = speaker,
            imageUrl = imageUrl
        )
    }
    // Пауза/продолжить
    fun playPause() {
        player?.let {
            if (it.isPlaying) it.pause() else it.play()
        }
    }
    // Перемотка
    fun seekTo(positionMs: Long) {
        player?.seekTo(positionMs.coerceIn(0, _uiState.value.duration))
    }
    // Перемотка вперёд на 15 сек
    fun skipForward(ms: Long = 15000) {
        val newPos = _uiState.value.currentPosition + ms
        seekTo(newPos)
    }
    // Перемотка назад на 15 сек
    fun skipBackward(ms: Long = 15000) {
        val newPos = _uiState.value.currentPosition - ms
        seekTo(newPos)
    }
    // Установить скорость
    fun setSpeed(speed: Float) {
        player?.setPlaybackSpeed(speed)
        _uiState.value = _uiState.value.copy(playbackSpeed = speed)
    }
    // Скрыть плеер
    fun hidePlayer() {
        player?.stop()
        _uiState.value = AudioPlayerUiState()
    }
    // Очистка ресурсов при уничтожении ViewModel
    override fun onCleared() {
        super.onCleared()
        player?.release()
        player = null
    }
}
// Состояние плеера
data class AudioPlayerUiState(
    val isPlayerVisible: Boolean = false,
    val isPlaying: Boolean = false,
    val isBuffering: Boolean = false,
    val currentPosition: Long = 0L,
    val duration: Long = 0L,
    val playbackSpeed: Float = 1.0f,
    val currentAudioUrl: String? = null,
    val bookName: String = "",
    val speaker: String = "",
    val imageUrl: String = ""
)