function getChatRoomListItem (room) {
  var li = '<a href="#" onClick="getMessages(event)" class="list-group-item" '
  li += 'data-room-id="' + room.id + '" data-room-ts="' + room.updated_at + '" '
  li += 'data-room-name="' + room.name + '" data-room-status="' + room.pivot.status
  li += '" style="word-wrap: break-word;">'
  li += room.name
  li += '</a>'
  return li
}

function getRooms () {
  $.ajax({
    url: '/rest/room/',
    method: 'GET',
    success: function (result) {
      result.sort(function (a, b) {
        da = new Date(a.updated_at)
        db = new Date(b.updated_at)
        if (da < db) {
          return -1
        }
        if (da > db) {
          return 1
        }
        return 0
      })

      var $chatRoomList = $('#chatRoomList')
      if ($chatRoomList.length) {
        var oldRooms = []
        var newRooms = []
        $.each($('#chatRoomList a'), function (i, obj) {
          oldRooms.push($(obj).data('room-id'))
        })
        for (var i = 0; i < result.length; i++) {
          var room = result[i]
          newRooms.push(room.id)
          var $item = $chatRoomList.children('a[data-room-id="' + room.id + '"]')
          if ($item.length) {
            var updated_at = $item.data('room-ts')
            if (updated_at != room.updated_at) {
              $item.remove()
              var li = getChatRoomListItem(room)
              $chatRoomList.html(li + $chatRoomList.html())
              var $sendMessage = $('#sendMessage')
              if ($sendMessage.length) {
                var selectedRoom = $sendMessage.data('room-id')
                if (selectedRoom == room.id) {
                  refreshMessages(selectedRoom)
                  getMembers(selectedRoom, room.pivot.status == 1)
                }
              }
            } else {
              var $sendMessage = $('#sendMessage')
              if ($sendMessage.length) {
                var selectedRoom = $sendMessage.data('room-id')
                if (selectedRoom == room.id) {
                  getMembers(selectedRoom, room.pivot.status == 1)
                }
              }
            }
          } else {
            var li = getChatRoomListItem(room)
            $chatRoomList.html(li + $chatRoomList.html())
          }
        }
        var deletedRooms = $(oldRooms).not(newRooms).get()
        $.each(deletedRooms, function (i, val) {
          var $deleted = $chatRoomList.children('a[data-room-id="' + val + '"]')
          $deleted.remove()
        })
      }
    },
    error: function (err) {
      console.log(err)
    }
  })
}

function getMessageLi (message, loginUserId) {
  var pullRight = loginUserId == message.user.id ? '<div class="pull-right">' : ''
  var closeDiv = loginUserId == message.user.id ? '</div>' : ''
  var textAlign = loginUserId == message.user.id ? 'text-align: right;' : 'text-align: left;'
  var li = '<li class="list-group-item row" data-message-id="' + message.id + '">'
  li += pullRight + '<strong class="list-group-item-heading">'
  li += message.user.name + '[' + message.user.email + ']'
  li += ' @ ' + message.created_at + '</strong>' + closeDiv + '<br/>'
  li += '<span class="list-group-item-text" style="width: 100%; word-wrap: break-word; display: inline-block; ' + textAlign + '">'
  li += message.message + '</span>'
  li += '</li>'
  return li
}

function getMemberLi (member, isAdmin) {
  var li = '<li class="list-group-item" data-user-id="' + member.id + '" '
  li += 'data-room-id="' + member.pivot.room_id + '" data-member-status="'
  li += member.pivot.status + '" data-member-ts="' + member.pivot.updated_at + '">'
  li += member.name + '[' + member.email + ']'
  if (isAdmin) {
    li += '<a href="#" onClick="kickMember(event)"><span class="glyphicon glyphicon-remove">Kick</span></a>'
    if (member.pivot.status == 1) {
      li += '<a href="#" onClick="demoteMember(event)"><span class="glyphicon glyphicon-minus">Demote</span></a> '
    } else {
      li += '<a href="#" onClick="promoteMember(event)"><span class="glyphicon glyphicon-plus">Promote</span></a>'
    }
  }
  li += '</li>'
  return li
}

function getMessages (event) {
  // localStorage.setItem('selectedRoom', roomId)
  var roomId = $(event.target).data('room-id')
  var roomName = $(event.target).data('room-name')
  var loginUserId = localStorage.getItem('userId')
  var isAdmin = $(event.target).data('room-status') == 1
  $.ajax({
    url: '/rest/room/member/message',
    method: 'GET',
    data: {
      roomId: roomId
    },
    success: function (result) {
      result.sort(function (a, b) {
        da = new Date(a.created_at)
        db = new Date(b.created_at)
        if (da < db) {
          return -1
        }
        if (da > db) {
          return 1
        }
        return 0
      })
      var $messageList = $('#chatMessages')
      if ($messageList.length) {
        $messageList.html('')
        for (var i = 0; i < result.length; i++) {
          var message = result[i]
          var li = getMessageLi(message, loginUserId)
          $messageList.append(li)
        }
        var $header = $('#chatRoomHeader')
        if ($header.length) {
          var leaveRoom = '<a href="#" class="pull-right" onClick="leaveRoom(' + roomId + ')">Leave<span class="glyphicon glyphicon-remove"></span></a>'
          $header.html(roomName + leaveRoom)
        }
        var $panel = $('#chatRoomPanel')
        if ($panel.length) {
          $panel.show()
        }
        var $sendMessage = $('#sendMessage')
        if ($sendMessage.length) {
          $sendMessage.data('room-id', roomId)
        }

        getMembers(roomId, isAdmin)
      }
    },
    error: function (err) {
      console.log(err)
    }
  })
}

function refreshMessages (roomId) {
  var loginUserId = localStorage.getItem('userId')
  var lastId = $('#chatMessages li').last().data('message-id')
  if (!lastId) {
    lastId = 0
  }
  $.ajax({
    url: '/rest/room/member/message/refresh',
    method: 'GET',
    data: {
      roomId: roomId,
      lastId: lastId
    },
    success: function (result) {
      var lis = ''
      for (var i = 0; i < result.length; i++) {
        var message = result[i]
        lis += getMessageLi(message, loginUserId)
      }

      var $messageUl = $('#chatMessages')
      if ($messageUl.length) {
        $messageUl.html($messageUl.html() + lis)
      }
    },
    error: function (err) {
      console.log(err)
    }
  })
}

function sendMessage (target) {
  var message = target.val()
  var roomId = target.data('room-id')
  $.ajax({
    url: '/rest/room/member/message',
    method: 'POST',
    data: {
      roomId: roomId,
      message: message
    },
    success: function (result) {
      target.val('')
    },
    error: function (err) {
      console.log(err)
    }
  })
}

function newRoom (target) {
  var name = target.val()
  $.ajax({
    url: '/rest/room/',
    method: 'POST',
    data: {
      name: name
    },
    success: function (result) {
      target.val('')
    },
    error: function (err) {
      console.log(err)
    }
  })
}

function getMembers (roomId, isAdmin) {
  var loginUserId = localStorage.getItem('userId')
  $.ajax({
    url: '/rest/room/member/',
    method: 'GET',
    data: {
      roomId: roomId
    },
    success: function (result) {
      result.sort(function (a, b) {
        da = new Date(a.name)
        db = new Date(b.name)
        if (da < db) {
          return 1
        }
        if (da > db) {
          return -1
        }
        return 0
      })
      var $panel = $('#membersPanel')
      if ($panel.length) {
        $panel.show()
      }

      var $footer = $('#membersFooter')
      if ($footer.length && isAdmin) {
        $footer.show()
        $('#addMember').data('room-id', roomId)
      } else if ($footer.length) {
        $footer.hide()
      }
      var $memberList = $('#chatRoomMemberList')
      if ($memberList.length) {
        var prevId = $panel.data('room-id')
        if (prevId != roomId) {
          $memberList.html('')
        }
        $panel.data('room-id', roomId)
        var oldMembers = []
        var newMembers = []
        $.each($('#chatRoomMemberList li'), function (i, obj) {
          oldMembers.push($(obj).data('user-id'))
        })
        for (var i = 0; i < result.length; i++) {
          var member = result[i]
          if (member.id == loginUserId) {
            continue
          }
          newMembers.push(member.id)
          var $item = $memberList.children('li[data-user-id="' + member.id + '"]')
          if ($item.length) {
            var updated_at = $item.data('member-ts')
            if (updated_at != member.pivot.updated_at) {
              $item.remove()
              var li = getMemberLi(member, isAdmin)
              $memberList.html(li + $memberList.html())
            }
          } else {
            var li = getMemberLi(member, isAdmin)
            $memberList.html(li + $memberList.html())
          }
        }
        var deletedMembers = $(oldMembers).not(newMembers).get()
        $.each(deletedMembers, function (i, val) {
          var $deleted = $memberList.children('li[data-user-id="' + val + '"]')
          $deleted.remove()
        })
      }
    },
    error: function (err) {
      console.log(err)
    }
  })
}

function leaveRoom (roomId) {
  $.ajax({
    url: '/rest/room/member/leave',
    method: 'POST',
    data: {
      roomId: roomId
    },
    success: function (result) {
      $('#chatRoomPanel').hide()
      $('#chatRoomHeader').html('')
      $('#chatMessages').html('')
      $('#membersPanel').hide()
      $('#chatRoomMemberList').html('')
      $('#membersFooter').hide()
      $sendMessage.data('room-id', 0)
      getRooms()
    },
    error: function (err) {
      console.log(err)
    }
  })
}

function kickMember (event) {
  var target = $(event.target)
  while (!target.is('li')) {
    target = target.parent()
  }
  var roomId = target.data('room-id')
  var userId = target.data('user-id')
  $.ajax({
    url: '/rest/room/admin/member/kick',
    method: 'POST',
    data: {
      roomId: roomId,
      userId: userId
    },
    success: function (result) {
      getMembers(roomId, true) // cannot perform this without being an admin
    },
    error: function (err) {
      console.log(err)
    }
  })
}

function promoteMember (event) {
  var target = $(event.target)
  while (!target.is('li')) {
    target = target.parent()
  }
  var roomId = target.data('room-id')
  var userId = target.data('user-id')
  $.ajax({
    url: '/rest/room/admin/new',
    method: 'POST',
    data: {
      roomId: roomId,
      userId: userId
    },
    success: function (result) {
      getMembers(roomId, true) // cannot perform this without being a member
    },
    error: function (err) {
      console.log(err)
    }
  })
}

function demoteMember (event) {
  var target = $(event.target)
  while (!target.is('li')) {
    target = target.parent()
  }
  var roomId = target.data('room-id')
  var userId = target.data('user-id')
  $.ajax({
    url: '/rest/room/admin/kick',
    method: 'POST',
    data: {
      roomId: roomId,
      userId: userId
    },
    success: function (result) {
      getMembers(roomId, true) // cannot perform this without being a member
    },
    error: function (err) {
      console.log(err)
    }
  })
}

function addMember (roomId, userId) {
  $.ajax({
    url: '/rest/room/admin/member/new',
    method: 'POST',
    data: {
      roomId: roomId,
      userId: userId
    },
    success: function (result) {
      getMembers(roomId, true)
    },
    error: function (err) {
      console.log(err)
    }
  })
}

$(document).ready(function () {
  $('#sendMessageButton').off('click').on('click', function () {
    sendMessage($('#sendMessage'))
  })
  $('#addGroupButton').off('click').on('click', function () {
    newRoom($('#addGroup'))
  })
  $('#leaveRoomButton').off('click').on('click', function () {
    leaveRoom()
  })
  $('#searchUserInput').off('change').on('change', function () {
    searchUsers()
  })
  $('#addMember').autocomplete({
    source: function (request, response) {
      $.ajax({
        url: '/rest/search',
        method: 'GET',
        data: {
          query: request.term
        },
        success: function (data) {
          var loginUserId = parseInt(localStorage.getItem('userId'), 10)
          var members = []
          $.each($('#chatRoomMemberList li'), function (i, obj) {
            members.push($(obj).data('user-id'))
          })
          if (!isNaN(loginUserId)) {
            members.push(loginUserId)
          }
          var autocompleteData = []
          for (var i = 0; i < data.length; i++) {
            var user = data[i]
            if ($.inArray(user.id, members) != -1) {
              continue
            }
            autocompleteData.push({
              label: user.name + '[' + user.email + ']',
              value: user.name + '[' + user.email + ']',
              userId: user.id
            })
          }
          response(autocompleteData)
        }
      })
    },
    minLength: 3,
    select: function (event, ui) {
      var roomId = $(event.target).data('room-id')
      var userId = ui.item.userId
      addMember(roomId, userId)
    },
    open: function () {
      $(this).removeClass('ui-corner-all').addClass('ui-corner-top')
    },
    close: function () {
      $(this).removeClass('ui-corner-top').addClass('ui-corner-all')
    }
  })
  getRooms()
  setInterval(getRooms, 2000)
})
