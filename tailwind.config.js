// const colors = require('tailwindcss/colors')
import forms from '@tailwindcss/forms'

export default {
  content: [
    "./resources/views/*.blade.php",
    "./resources/js/components/**/*.vue",
  ],
  theme: {
    // colors: {
    //   info: colors.blue[600],
    //   warning: colors.amber[300],
    //   primary: colors.teal[500],
    //   success: colors.green[500]
    // },
    extend: {
      colors: {
        yellow: {
          '75': '#FEFBD6'
        },
        green: {
          '75': '#E6FDEE'
        },
        gray: {
          '350': '#B7BCC5'
        },
      },
      spacing: {
        '128': '32rem',
        '160': '40rem',
      }
    },
    minWidth: {
      60: '15rem',
    }
  },
  plugins: [
    forms,
  ],
}
