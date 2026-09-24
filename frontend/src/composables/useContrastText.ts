import { ref, watch, onMounted } from 'vue'

/**
 * Composable to automatically adjust text color based on background color
 * - White background (#ffffff, white) → Black text (#000000)
 * - Black background (#000000, black) → White text (#ffffff)
 * - Other colors → No change
 */
export function useContrastText(elementRef: any) {
  const textColor = ref('')

  const getContrastColor = (backgroundColor: string): string => {
    // Normalize the background color string
    const bg = backgroundColor.toLowerCase().trim()

    // Check for white backgrounds
    if (
      bg === '#ffffff' ||
      bg === '#fff' ||
      bg === 'white' ||
      bg === 'rgb(255, 255, 255)' ||
      bg === 'rgba(255, 255, 255, 1)'
    ) {
      return '#000000'
    }

    // Check for black backgrounds
    if (
      bg === '#000000' ||
      bg === '#000' ||
      bg === 'black' ||
      bg === 'rgb(0, 0, 0)' ||
      bg === 'rgba(0, 0, 0, 1)'
    ) {
      return '#ffffff'
    }

    // For other colors, return empty string (no change)
    return ''
  }

  const updateTextColor = () => {
    if (!elementRef.value) return

    const computedStyle = window.getComputedStyle(elementRef.value)
    const backgroundColor = computedStyle.backgroundColor
    textColor.value = getContrastColor(backgroundColor)

    if (textColor.value) {
      elementRef.value.style.color = textColor.value
    }
  }

  onMounted(() => {
    updateTextColor()
  })

  // Watch for changes in the element ref
  watch(elementRef, () => {
    updateTextColor()
  })

  // Also watch for style changes using MutationObserver
  onMounted(() => {
    if (!elementRef.value) return

    const observer = new MutationObserver(() => {
      updateTextColor()
    })

    observer.observe(elementRef.value, {
      attributes: true,
      attributeFilter: ['style', 'class']
    })

    // Cleanup on unmount
    return () => {
      observer.disconnect()
    }
  })

  return {
    textColor,
    updateTextColor
  }
}
