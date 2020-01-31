import React, { Component } from 'react';
import { View, TouchableHighlight, Text, Image } from 'react-native';

export default class TouchableHighlightDemo extends Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    return (
      <View style={{ top: 100, left: 100 }}>
        <TouchableHighlight
          activeOpacity= { 0.5}
          underlayColor ='#ccc'
          onLongPress={() => {
            console.log("long press")
          }}
          onPress={() => {
            console.log("press")
          }}
          onPressIn={() => {
            console.log("press in")
          }}
          onPressOut={() => {
            console.log("press out")
          }}
        >
          <View>
            <Image source={require('../source/bamboo.jpg')}
              style={{ width: 50, height: 50 }}
            />
            <Text>Button</Text>
          </View>
        </TouchableHighlight>
      </View>
    );
  }
}
